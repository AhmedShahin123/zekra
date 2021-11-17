<?php

namespace App\Http\Controllers\API\V2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\Order\OrderServiceInterface;

use App\Http\Resources\Order as ResourcesOrder;

use App\Models\Album;
use App\Models\City;
use App\Models\Coupon;
use App\Models\CouponUser;
use App\Models\Courier;
use App\Models\CourierPrice;
use App\Models\CourierZone;
use App\Models\Order;
use App\Models\Partner;
use App\Models\Product;
use App\Models\partnerOrder;
use App\Models\courierOrder;
use App\Models\Image;
use App\Models\Payment;
use App\Models\Receipt;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\User;
use App\Notifications\Transaction as NotificationsTransaction;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

use App\Services\Order\OrderPrice;


use Symfony\Component\Filesystem\Filesystem;
use Xthiago\PDFVersionConverter\Converter\GhostscriptConverterCommand;
use Xthiago\PDFVersionConverter\Converter\GhostscriptConverter;

use App\Traits\Helper;
use App\Traits\Payment as PaymentHelper;

use Milon\Barcode\DNS1D;
use Carbon\Carbon;
use Image as CreateImage;
use PDF;
use Stripe;
use Auth;
use App;
use App\Models\UserAddress;

class OrdersController extends Controller
{
    use PaymentHelper;
    use Helper;

    public $orderService;

    public function __construct(OrderServiceInterface $orderService)
    {
        $this->orderService = $orderService;
    }

    public function addOrder(Request $request)
    {
        $user = auth()->user();

        // validate the request
        $rules = [
            'albums'       => 'required'
        ];
        $validator = Validator::make(request()->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'msg' => $validator->errors()->first()], 400);
        }

        $inputs = request()->only('albums');

        // create new cart
        $cartData = [
            'cart_total'        => 0,
            'cart_total_fee'    => 0,
            'cart_total_tax'    => 0,
            'total_price'       => 0
        ];
        $cart = $user->cart()->create($cartData);

        // create new order
        $now = Carbon::now();
        $shipping_address = $user->formatAddress();
        $shipping_phone = $user->phone ? $user->phone : '';
        $orderData = [
            'cart_id'               => $cart->id,
            'shipping_address'      => $shipping_address,
            'shipping_phone'        => $shipping_phone,
            'fee'                   => 0,
            'tax'                   => 0,
            'total'                 => 0,
            'progress_status'       => 'Order Received',
            'delivery_status'       => 'Ready for Pickup',
            'payment_status'        => 'Not Paid',
            'progress_status_date'  => $now->toDateTimeString(),
            'delivery_status_date'  => $now->toDateTimeString(),
        ];
        $order = $user->orders()->create($orderData);

        // create the album images
        if (request()->has('albums')) {
            $albums=request('albums');
            foreach ($albums as $oneAlbum) {
                $oneAlbum = json_decode($oneAlbum);
                $albumImages = $oneAlbum->images;

                $albumData = [
                    'album_name'    => $oneAlbum->album_name,
                    'album_count'   => $oneAlbum->album_count,
                    'order_id'      => $order->id,
                    'cover_id'      => 0,
                    'album_status'  => 0
                ];
                $album = $user->albums()->create($albumData);

                foreach ($albumImages as $image) {
                    $file_data = $image;  // your base64 encoded
                    //generating unique file name;
                    $file_name = str_random(10).'image_'.time().'.jpg';
                    @list($type, $file_data) = explode(';', $file_data);
                    @list(, $file_data)      = explode(',', $file_data);

                    Storage::put("albums/$album->id/$file_name", base64_decode($file_data));

                    $imageData = [
                        'image_path'    => "storage/app/public/albums/$album->id/",
                        'image_name'    => $file_name,
                        'image_size'    => Storage::size("albums/$album->id/$file_name"),
                    ];
                    $album->albumImages()->create($imageData);
                }

                // create album pdf file
                $pdfFileName = $this->createAlbumPdfFile($album->id);
                $album->update(['album_pdf' => $pdfFileName]);
            }
        }

        $albumsCount = Album::where('order_id', $order->id)->pluck('album_count');
        $orderCount = array_sum($albumsCount->toArray());
        $order->album_count = $orderCount;
        $order->save();

        $partner = Partner::where('city_id', $order->user->city_id)->where('default', 1)->first();
        if ($partner) {
            $partnerOrder = new partnerOrder([
                'partner_id' => $partner->id,
                'order_id' => $order->id,
                'status' => 0
            ]);
            $partnerOrder->save();

            $courierPartner = Courier::where('city_id', $partner->city_id)->where('default', 1)->first();
            if ($courierPartner) {
                $courierOrder = new courierOrder([
                    'courier_id' => $courierPartner->id,
                    'order_id' => $order->id,
                    'status' => 0
                ]);
                $courierOrder->save();
                $courierShipping = $this->courierShipping($courierPartner->user_id, $order->id);
                $partnerShipping = $this->partnerTax($courierShipping, $order->id, $partner->user_id);
            } else {
                $courierOrder = new courierOrder([
                    'courier_id' => 1,
                    'order_id' => $order->id,
                    'status' => 0
                ]);
                $courierOrder->save();

                $courierShipping = $this->courierShipping($courierOrder->courier->user_id, $order->id);
                $partnerShipping = $this->partnerTax($courierShipping, $order->id, $partner->user_id);
            }
        } else {
            $partnerOrder = new partnerOrder([
                'partner_id' => 1,
                'order_id' => $order->id,
                'status' => 0
            ]);
            $partnerOrder->save();

            $courierOrder = new courierOrder([
                'courier_id' => 1,
                'order_id' => $order->id,
                'status' => 0
            ]);
            $courierOrder->save();

            $courierShipping = $this->courierShipping($courierOrder->courier->user_id, $order->id);
            $partnerShipping = $this->partnerTax($courierShipping, $order->id, $partnerOrder->partner->user_id);
        }
        $albumPrice = Setting::where('key', 'album_price')->pluck('value')->first();
        $total = $albumPrice * Album::where('order_id', $order->id)->get()->count();
        $userTax = $total * $order->user->city->tax / 100;
        $order->fee = $courierShipping;
        $order->tax = $partnerShipping;
        $order->total = $courierShipping + $partnerShipping + $total + $userTax;
        $order->save();

        $receipt = $this->createReceipt($order->id);
        $orderReceipt = new Receipt([
            'receipt' => $receipt,
            'order_id' => $order->id
        ]);
        $orderReceipt->save();

        $transaction = new Transaction([
            'zekra_payments' => $total,
            'partner_payments' => $partnerShipping,
            'taxes' => $userTax,
            'shipping' => $courierShipping,
            'payment_method' => 'online_payment',
            'collector' => 'zekraHQ',
            'country_id' => $order->user->country_id,
            'city_id' => $order->user->city_id,
            'order_id' => $order->id,
            'status' => $order->payment_status
        ]);

        $transaction->save();

        $user = User::where('id', $order->user_id)->first();
        try {
            $user->sendOrderNotification($order);
        } catch (\Exception $e) {
        }



        $request->request->add(['order_id' => $order->id,'stripeToken' => 'tok_visa']);
        $stripteStatus = $this->stripePost($request->all());
        $order = $stripteStatus;
        $response = new ResourcesOrder($order);
        if ($stripteStatus) {
            return response()->json(['status' => true, 'msg' => 'order created successfully', 'data' => $response], 200);
        }
        return response()->json(['status' => false, 'msg' => 'payment failed', 'data' => $response, 'stripeData'=> $stripteStatus], 200);
    }

    public function stripePost($request)
    {
        //dd($request);
        $order = Order::find($request['order_id']);
        //dd($order);

        $amount = $order->total;
        if (!empty($request['discount_code'])) {
            // check for this code
            $coupon = Coupon::where('code', $request->get('discount_code'))->first();
            if (empty($coupon)) {
                return response()->json(['status'=>false,'msg' => 'The applied discount code is incorrect'], 400);
            }

            // validate this coupon
            $validation = $this->validateCoupon($coupon);
            if ($validation['failed']) {
                return response()->json(['status' => false, 'msg' => $validation['message']], 400);
            }

            $orderData = [
                'coupon_id'         => $coupon->id,
                'discount_value'    => $coupon->value,
                'discount_code'     => $coupon->code
            ];
            $amount -= $coupon->value;
        }
        $amount = round($amount);


        Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
        $payment = Stripe\Charge::create([
              "amount" => $amount,
              "currency" => "usd",
              "source" => $request['stripeToken'],
              "description" => "Request Order"
        ]);

        //return $payment['status'];

        if ($payment['status'] == 'succeeded') {
            $mytime = Carbon::now();


            $order->update(['payment_status' => "Paid", 'card_token' => $payment['payment_method'], 'stripe_transaction_date' => $mytime->toDateTimeString(), 'stripe_id' => $payment['id']]);
            //dd($order);
            $transaction = Transaction::where('order_id', $order->id)->first();
            $transaction->status = $order->payment_status;
            $transaction->save();

            return $order;
        }

        return $payment;
    }

    public function reOrder()
    {
        $validator = Validator::make(request()->all(), [
            'order_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'msg' => $validator->errors()->first()], 200);
        }
        $oldOrder = Order::find(request('order_id'));
        if (empty($oldOrder)) {
            return response()->json(['status' => false, 'msg' => 'order not found'], 200);
        }

        $mytime = Carbon::now();
        $data['user_id'] = Auth::id();
        $data['shipping_address'] = $oldOrder->shipping_address;
        $data['shipping_phone'] = $oldOrder->shipping_phone;
        $data['fee'] = $oldOrder->fee;
        $data['tax'] = $oldOrder->tax;
        $data['total'] = $oldOrder->total;
        $data['album_count'] = $oldOrder->album_count;
        $data['cart_id'] = $oldOrder->cart_id;
        $data['progress_status'] = "Order Received";
        $data['delivery_status'] = "Ready for Pickup";
        $data['payment_status'] = "Not Paid";
        $data['progress_status_date'] = $mytime->toDateTimeString();
        $data['delivery_status_date'] = $mytime->toDateTimeString();
        $data['card_token'] = null;
        $data['stripe_transaction_date'] = null;

        $order = auth()->user()->orders()->create($data);

        $album = Album::where('order_id', request('order_id'))->first();

        $albumdata['user_id'] = Auth::id();
        $albumdata['cover_id'] = $album->cover_id;
        $albumdata['album_name'] = $album->album_name;
        $albumdata['album_status'] = $album->album_status;
        $albumdata['album_pdf'] = $album->album_pdf;
        $albumdata['order_id'] = $order->id;
        $newAlbum = auth()->user()->albums()->create($albumdata);

        $images = Image::where('album_id', $album->id)->get();
        foreach ($images as $key => $image) {
            // code...
            $imageData['album_id'] = $newAlbum->id;
            $imageData['image_path'] = $image->image_path;
            $imageData['image_name'] = $image->image_name;
            $imageData['image_size'] = $image->image_size;

            $newImage = new Image($imageData);
            $newImage->save();
        }


        if ($order) {
            $coupon = $this->createInvitationCoupon($order->id);
            $albumID = Album::where('user_id', $order->user_id)->latest()->first()->id;
            $albumImages = Image::where('album_id', $albumID)->pluck('image_name');
            $album = Album::find($albumID);
            $coverImageName = $this->createCoverPhoto($albumID);

            $html = "<tr><td><img src=" . storage_path('app/public/' . $coverImageName) . "  height=" . '580px' . " width=" . '100%' . "></td></tr>";
            foreach ($albumImages as $key => $albumImage) {
                $html = $html . '<tr><td rowspan="5"><img src="' . storage_path('app/public') . '/' . $albumImage . '" alt="Logo" height="580px" width="100%"></td></tr>';
            }
            $html = $html . "<tr><td><p>Invite " . $coupon->usage_times . " of your friends and let them get " . $coupon->value . "$ now!</p><p>" . $coupon->code . "</p></td></tr>";

            $pdf = App::make('dompdf.wrapper');
            $pdf->setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true])->loadHTML($html);
            Storage::put('public/pdf/invoice' . $order->id . '.pdf', $pdf->output());

            $album->album_pdf = "invoice$order->id";
            $album->save();

            $partner = Partner::where('city_id', $order->user->city_id)->where('default', 1)->first();
            if ($partner) {
                $partnerOrder = new partnerOrder([
                    'partner_id' => $partner->id,
                    'order_id' => $order->id,
                    'status' => 0
                ]);
                $partnerOrder->save();

                $courierPartner = Courier::where('city_id', $partner->city_id)->where('default', 1)->first();
                if ($courierPartner) {
                    $courierOrder = new courierOrder([
                        'courier_id' => $courierPartner->id,
                        'order_id' => $order->id,
                        'status' => 0
                    ]);
                    $courierOrder->save();
                    $courierShipping = $this->courierShipping($courierPartner->user_id, $order->id);
                    $partnerShipping = $this->partnerTax($courierShipping, $order->id, $partner->user_id);
                } else {
                    $courierOrder = new courierOrder([
                        'courier_id' => 1,
                        'order_id' => $order->id,
                        'status' => 0
                    ]);
                    $courierOrder->save();

                    $courierShipping = $this->courierShipping($courierOrder->courier->user_id, $order->id);
                    $partnerShipping = $this->partnerTax($courierShipping, $order->id, $partner->user_id);
                }
            } else {
                $partnerOrder = new partnerOrder([
                    'partner_id' => 1,
                    'order_id' => $order->id,
                    'status' => 0
                ]);
                $partnerOrder->save();

                $courierOrder = new courierOrder([
                    'courier_id' => 1,
                    'order_id' => $order->id,
                    'status' => 0
                ]);
                $courierOrder->save();

                $courierShipping = $this->courierShipping($courierOrder->courier->user_id, $order->id);
                $partnerShipping = $this->partnerTax($courierShipping, $order->id, $partnerOrder->partner->user_id);
            }
            $albumPrice = Setting::where('key', 'album_price')->pluck('value')->first();
            $total = $albumPrice * $order->album_count;
            $order->fee = $courierShipping;
            $order->tax = $partnerShipping;
            $order->total = $courierShipping + $partnerShipping + $total;
            $order->save();

            $transaction = new Transaction([
                'zekra_payments' => $total,
                'partner_payments' => $partnerShipping,
                'taxes' => $order->user->city->tax,
                'shipping' => $courierShipping,
                'payment_method' => 'online_payment',
                'collector' => 'zekraHQ',
                'country_id' => $order->user->country_id,
                'city_id' => $order->user->city_id,
                'order_id' => $order->id,
                'status' => $order->payment_status
            ]);

            $transaction->save();

            $user = User::where('id', $order->user_id)->first();
            try {
                $user->sendOrderNotification($order);
            } catch (\Exception $e) {
            }

            return response()->json(['status' => true, 'msg' => 'order created successfully', 'data' => $order], 200);
        }

        return response()->json(['status' => false, 'msg' => 'order not created'], 200);
    }

    public function courierShipping($courier_id, $order_id)
    {
        $courier = Courier::where('user_id', $courier_id)->first();
        $order = Order::find($order_id);

        if (empty($courier)) {
            return response()->json(['status' => false, 'msg' => 'courier not found'], 200);
        }

        if (empty($order)) {
            return response()->json(['status' => false, 'msg' => 'order not found'], 200);
        }

        $albumCount = Album::where('order_id', $order->id)->get()->count();
        $albumSize = ($albumCount * 400) / 1000;
        //dd($albumSize);
        $courierPrice = CourierPrice::where('courier_id', $courier->id)->first();
        if (empty($courierPrice)) {
            return response()->json(['status' => false, 'msg' => 'courier price not found'], 200);
        }

        if ($albumSize <= $courierPrice->primary_weight) {
            $itemPrice = $courierPrice->primary_weight_price / $courierPrice->primary_weight;
            //dd($itemPrice);
            $courierShipping = $itemPrice * $albumSize;
        } else {
            $itemPrice = $courierPrice->primary_weight_price / $courierPrice->primary_weight;
            //dd($itemPrice);
            $courierShipping = $itemPrice * $courierPrice->primary_weight;
            //dd($courierShipping);
            $additionalSize = $albumSize - $courierPrice->primary_weight;
            //dd($additionalSize);
            $additionalItemPrice = $courierPrice->additional_weight_price / $courierPrice->additional_weight;
            //dd($additionalItemPrice);
            $courierShipping = $additionalItemPrice * $additionalSize + $courierShipping;
        }

        //return response()->json(['status' => true, 'data' => $courierShipping], 200);
        return $courierShipping;
    }

    public function partnerTax($courierShipping, $order_id, $partner_id)
    {
        $order = Order::find($order_id);
        $partner = Partner::where('user_id', $partner_id)->first();

        if (empty($order)) {
            return response()->json(['status' => false, 'msg' => 'order not found'], 200);
        }
        //  $courierShipping = 10;
        $albumPrice = Setting::where('key', 'album_price')->pluck('value')->first();
        $albumCount = Album::where('order_id', $order->id)->get()->count();
        $total = $albumPrice * $albumCount;
        $partnerTax = ($total + $courierShipping) * $partner->fee / 100;

        return $partnerTax;
    }

    /**
        * @SWG\GET(
        *     path="/api/v2/myOrders",
        *     description="get user orders",
        *     tags = {"orders"},
        *   security={{"Bearer":{}}},
        *     @SWG\Response(
        *         response=200,
        *         description="",
        *        examples={
        *     "application/json": { "status": true, "data": {} }
        *      }
        *     ),
        *     @SWG\Response(
        *         response=401,
        *         description="",
        *        examples={
        *     "application/json": { "status": false, "msg": "Unauthorized" }
        *      }
        *     )
        *  )
        */


    public function myOrders()
    {
        $userOrders = $this->orderService->userOrders(auth()->id());
        if ($userOrders) {
            $response = ResourcesOrder::collection($userOrders);
            return response()->json(['status' => true, 'data' => $response], 200);
        }
        return response()->json(['status' => false, 'msg' => 'there is no orders'], 401);
    }

    /**
        * @SWG\Post(
        *     path="/api/v2/orderDetails",
        *     description="order Details",
        *     tags = {"orders"},
        *   security={{"Bearer":{}}},
        *     @SWG\Parameter(
        *         name="order_id",
        *         in="query",
        *         type="integer",
        *         description="order_id",
        *         required=true,
        *     ),
        *     @SWG\Response(
        *         response=200,
        *         description="",
        *        examples={
        *     "application/json": { "status": true, "data": {} }
        *      }
        *     ),
        *     @SWG\Response(
        *         response=401,
        *         description="",
        *        examples={
        *     "application/json": { "status": false, "msg": "Unauthorized" }
        *      }
        *     )
        *  )
        */

    public function orderDetails()
    {
        $validator = Validator::make(request()->all(), ['order_id' => 'required']);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'msg' => $validator->errors()->first()], 400);
        }

        $order = Order::where('user_id', auth()->id())->find(request('order_id'));


        if (empty($order)) {
            return response()->json(['status' => false, 'msg' => 'Order not found'], 200);
        }

        $order->user_name = empty($order->user) ? '' : $order->user->name;
        $order->partnerOrders = empty($order->partnerOrders) ? '' : $order->partnerOrders;
        $order->courierOrders = empty($order->courierOrders) ? '' : $order->courierOrders;
        $order->album = Album::where('order_id', request('order_id'))->with('albumImages')->latest()->get();

        $response = new ResourcesOrder($order);
        return response()->json(['status' => true,'data' => $response]);
    }

    /**
        * @SWG\Post(
        *     path="/api/v2/user/orders/1/refund",
        *     description="refund order by id",
        *     tags = {"refunds"},
        *   security={{"Bearer":{}}},
        *     @SWG\Response(
        *         response=200,
        *         description="",
        *        examples={
        *     "application/json": { "status": true, "data": {} }
        *      }
        *     ),
        *     @SWG\Response(
        *         response=401,
        *         description="",
        *        examples={
        *     "application/json": { "status": false, "msg": "Unauthorized" }
        *      }
        *     )
        *  )
        */

    public function refundOrder($id)
    {
        $order = auth()->user()->orders()->find($id);

        // check if this order exists
        if (empty($order)) {
            return response()->json(['status' => false, 'msg' => 'Order not found'], 404);
        }

        // check if this order is refundable
        if (!$order->refundable) {
            return response()->json(['status' => false, 'msg' => 'This order already has a refund request'], 400);
        }

        $order->refund()->create();
        $order = Order::find($order->id);  // get refreshed copy of the order with the new updated data (refundable => true)
        $response = new ResourcesOrder($order);
        return response()->json(['status' => true, 'msg' => 'The refund request created successfully', 'data' => $response]);
    }

    /**
     * This method is responsible for create new order record
     * Validate that the user has a shipping address
     * Assign order partner
     * Assign order courier
     */

    /**
        * @SWG\Post(
        *     path="/api/v2/orders",
        *     description="create an order",
        *     tags = {"orders"},
        *   security={{"Bearer":{}}},
        *     @SWG\Parameter(
        *         name="address_id",
        *         in="query",
        *         type="integer",
        *         description="address_id",
        *         required=true,
        *     ),
        *     @SWG\Response(
        *         response=200,
        *         description="",
        *        examples={
        *     "application/json": { "status": true, "data": {} }
        *      }
        *     ),
        *     @SWG\Response(
        *         response=401,
        *         description="",
        *        examples={
        *     "application/json": { "status": false, "msg": "Unauthorized" }
        *      }
        *     )
        *  )
        */

    public function createOrder()
    {
        $user = auth()->user();
        $userAddressesIds = $user->addresses->pluck('id')->toArray();

        // validate that the user has a shipping address
        $inputs = request()->only('address_id');
        $rules = [
            'address_id'    => ['required', Rule::in($userAddressesIds)]
        ];
        $validation = Validator::make($inputs, $rules);
        if ($validation->fails()) {
            return response()->json(['status' => false, 'msg' => $validation->errors()->first()], 400);
        }

        $shippingAddress = $user->addresses()->find($inputs['address_id']);
        // create order record
        $orderData = [
            'user_id'               => $user->id,
            'shipping_address_id'   => $shippingAddress->id,
            'shipping_address'      => $shippingAddress->getLongAddress(),
            'shipping_phone'        => $shippingAddress->phone ? $shippingAddress->phone : '',
            'fee'                   => 0,
            'tax'                   => 0,
            'total'                 => 0,
            'progress_status_date'  => Carbon::now(),
            'delivery_status_date'  => Carbon::now()
        ];
        $order = Order::create($orderData);
        $order = Order::find($order->id);

        $partner = $this->getOrderPartner($order);
        $order->update(['partner_id' => $partner->id]);


        $courier = $this->getOrderCourier($order);
        $order->update(['courier_id' => $courier->id]);

        $response = new ResourcesOrder($order);
        return response()->json(['status' => true, 'msg' => 'Order created successfully', 'data' => $response], 201);
    }

    /**
     * This method is responsible for assign a partner to the order based on user location
     */
    private function getOrderPartner(Order $order)
    {
        $partner = Partner::where('city_id', $order->shippingAddress->city_id)->where('default', 1)->first();
        if (empty($partner)) { // if there is not default partner got to zekraHQ partner
            $partner = Partner::first();
        }

        return $partner;
    }

    /**
     * This method is responsible for assign a courier to the order based on partner location
     */
    private function getOrderCourier(Order $order)
    {
        $courier = Courier::where('city_id', $order->partner->city_id)->where('default', 1)->first();
        if (empty($courier)) { // if there is not default partner got to zekraHQ courier
            $courier = Courier::first();
        }

        return $courier;
    }

    /**
     * This method is responsible for paying for an order
     */

    /**
        * @SWG\PUT(
        *     path="/api/v2/orders/1/pay",
        *     description="pay for order with order id",
        *     tags = {"orders"},
        *   security={{"Bearer":{}}},
        *     @SWG\Parameter(
        *         name="albums_count",
        *         in="query",
        *         type="integer",
        *         description="albums_count",
        *         required=true,
        *     ),
        *     @SWG\Parameter(
        *         name="payment_method",
        *         in="query",
        *         type="string",
        *         description="payment_method options credit_card , credit_points",
        *         required=true,
        *     ),
        *     @SWG\Parameter(
        *         name="discount_code",
        *         in="query",
        *         type="string",
        *         description="discount_code",
        *         required=false,
        *     ),
        *     @SWG\Response(
        *         response=200,
        *         description="",
        *        examples={
        *     "application/json": { "status": true, "data": {} }
        *      }
        *     ),
        *     @SWG\Response(
        *         response=401,
        *         description="",
        *        examples={
        *     "application/json": { "status": false, "msg": "Unauthorized" }
        *      }
        *     )
        *  )
        */


    public function payOrder($id)
    {
        $user = auth()->user();
        $order = $user->orders()->find($id);
        if (empty($order)) {
            return response()->json(['status' => false, 'msg' => 'Order not found'], 404);
        }

        if ($order->is_paid) {
            $response = new ResourcesOrder($order);
            return response()->json(['status' => true, 'msg' => 'This order has already paid', 'data' => $response]);
        }

        $inputs = request()->only('albums_count', 'payment_method', 'discount_code', 'card_id');
        $allowedPaymentMethods = $this->getAllowedPaymentMethods();
        $rules = [
            'albums_count'      => 'required|numeric|min:1',
            'payment_method'    => ['required', Rule::in($allowedPaymentMethods)]
        ];
        $validation = Validator::make($inputs, $rules);
        if ($validation->fails()) {
            return response()->json(['status' => false, 'msg' => $validation->errors()->first()], 400);
        }
        
        $shippingAddress = $order->shippingAddress;
        $orderPrice = new OrderPrice($user, $shippingAddress);
        $orderPrice->useOrder($order);
        $priceVariables = $orderPrice->getPriceVariables($inputs['albums_count']);

        if (request()->has('discount_code')) {
            $discountCode = $inputs['discount_code'];
            // check for this code
            $coupon = Coupon::where('code', $discountCode)->first();
            if (empty($coupon)) {
                return response()->json(['status' => false,'msg' => 'The applied discount code is incorrect'], 400);
            }

            // validate this coupon
            $validation = $this->validateCoupon($coupon);
            if ($validation['failed']) {
                return response()->json(['status' => false, 'msg' => $validation['message']], 400);
            }

            $couponData = [
                'coupon_id'     => $coupon->id,
                'user_id'       => $user->id,
                'order_id'      => $order->id,
                'coupon_code'   => $coupon->code,
                'value_type'    => $coupon->value_type,
                'value'         => $coupon->value
            ];
            if ($coupon->value_type == 'money') {
                // discount the coupon value from the order total price
                $priceVariables['total'] -= $coupon->value;
            } elseif ($coupon->value_type == 'points') {
                // increase the user points by the value of the coupon by saving the coupon in coupon_user table
                CouponUser::create($couponData);
            }
        }

        // pay for the order
        if ($inputs['payment_method'] == 'credit_card') {

            if (count($user->cards) == 0) {
                return response()->json(['status' => false, 'msg' => 'User does not have a default card'], 400);
            }

            // pay for the order using user default credit card
            $card = $user->card;
            if(request()->has('card_id')){
                $card = $user->cards()->find($inputs['card_id']);
            }

            if (empty($card)) {
                return response()->json(['status' => false, 'msg' => 'Selected credit card is invalid'], 400);
            }

            
            $payment = $this->handlePayment($card->card_token, $priceVariables['total']);
            $paymentData = [
                'user_id'               => $user->id,
                'purchased_type'        => Order::class,
                'purchased_id'          => $order->id,
                'payment_method'        => $inputs['payment_method'],
                'money_amount'          => $priceVariables['total'],
                'points_amount'         => $priceVariables['points'],
                'card_token'            => $card->card_token,
                'payment_provider'      => 'stripe',
            ];
            if ($payment['failed']) {
                // store failed payment record
                $paymentData['status']      = 0;
                $paymentData['extra_data']  = ['error_response' => $payment['data']];
                Payment::create($paymentData);
                return response()->json(['status' => false, 'msg' => 'Payment failed', 'data' => $payment['data']], 400);
            } else {
                // store success payment record
                $paymentData['status']              = 1;
                $paymentData['payment_provider_id'] = $payment['data']->id;

                // update the order payment status to be paid
                $orderData = [
                    'fee'               => $priceVariables['shipping'],
                    'tax'               => $priceVariables['taxes'],
                    'total'             => $priceVariables['total'],
                    'album_count'       => $inputs['albums_count'],
                    'payment_status'    => 'Paid'
                ];
                $order->update($orderData);
            }
            $createdPayment = Payment::create($paymentData);

            // store the coupon with the user coupons
            if (request()->has('discount_code') && $coupon->value_type == 'money') {
                CouponUser::create($couponData);
            }
        } elseif ($inputs['payment_method'] == 'credit_points') {
            if ($user->credit_points < $priceVariables['points']) {
                return response()->json(['status' => false, 'msg' => 'User credit points is not enough'], 400);
            }

            $paymentData = [
                'user_id'               => $user->id,
                'purchased_type'        => Order::class,
                'purchased_id'          => $order->id,
                'payment_method'        => $inputs['payment_method'],
                'money_amount'          => $priceVariables['total'],
                'points_amount'         => $priceVariables['points'],
                'status'                => 1
            ];
            $createdPayment = Payment::create($paymentData);
            // store the coupon with the user coupons
            if (request()->has('discount_code') && $coupon->value_type == 'money') {
                CouponUser::create($couponData);
            }

            // update the order payment status to be paid
            $orderData = [
                'fee'               => $priceVariables['shipping'],
                'tax'               => $priceVariables['taxes'],
                'total'             => $priceVariables['total'],
                'album_count'       => $inputs['albums_count'],
                'payment_status'    => 'Paid'
            ];
            $order->update($orderData);
        } elseif ($inputs['payment_method'] == 'cod'){
            $priceVariables['total'] += $priceVariables['cod'];

            $paymentData = [
                'user_id'               => $user->id,
                'purchased_type'        => Order::class,
                'purchased_id'          => $order->id,
                'payment_method'        => $inputs['payment_method'],
                'money_amount'          => $priceVariables['total'],
                'points_amount'         => $priceVariables['points'],
                'status'                => 1
            ];
            $createdPayment = Payment::create($paymentData);
            // store the coupon with the user coupons
            if (request()->has('discount_code') && $coupon->value_type == 'money') {
                CouponUser::create($couponData);
            }

            // update the order payment status to be paid
            $orderData = [
                'fee'               => $priceVariables['shipping'],
                'tax'               => $priceVariables['taxes'],
                'total'             => $priceVariables['total'],
                'album_count'       => $inputs['albums_count'],
                'payment_status'    => 'Paid'
            ];
            $order->update($orderData);
        }

        // store transactions records
        $partnerPayments = $priceVariables['subtotal'] * ($order->partner->fee / 100);
        $zekraPayments = $priceVariables['subtotal'] - $partnerPayments;
        $transactionsTypes = [
            'zekra_payments'    => $zekraPayments,
            'partner_payments'  => $partnerPayments,
            'taxes'             => $priceVariables['taxes'],
            'shipping'          => $priceVariables['shipping'],
        ];
        if($inputs['payment_method'] == 'cod'){
            $transactionsTypes['cod'] = $priceVariables['cod'];
        }

        foreach($transactionsTypes as $type => $value){
            $transactionData = [
                'payment_id'    => $createdPayment->id,
                'type'          => $type,
                'amount'        => $value
            ];
            Transaction::create($transactionData);
        }

        // notify the admins with the new transactions
        $admins = $this->getAdmins();
        Notification::send($admins, new NotificationsTransaction('info', 'New transactions', '/admin/resources/transactions'));

        $order = Order::find($order->id);
        $response = new ResourcesOrder($order);
        return response()->json(['status' => true, 'msg' => 'Order paid successfully', 'data' => $response]);
    }


    /**
        * @SWG\GET(
        *     path="/api/v2/orders/price",
        *     description="get order prices",
        *     tags = {"orders"},
        *   security={{"Bearer":{}}},
        *     @SWG\Parameter(
        *         name="albums_count",
        *         in="query",
        *         type="integer",
        *         description="albums_count",
        *         required=true,
        *     ),
        *     @SWG\Response(
        *         response=200,
        *         description="",
        *        examples={
        *     "application/json": { "status": true, "data": {} }
        *      }
        *     ),
        *     @SWG\Response(
        *         response=401,
        *         description="",
        *        examples={
        *     "application/json": { "status": false, "msg": "Unauthorized" }
        *      }
        *     )
        *  )
        */

    public function getPriceVariables()
    {
        $user = auth()->user();
        $address = null;
        if (request()->has('address_id')) {
            $address = $user->addresses()->find(request('address_id'));
            if (empty($address)) {
                return response()->json(['status' => false, 'msg' => 'address not found'], 404);
            }
        }

        $albumsCount = request()->has('albums_count') ? request('albums_count') : 1;

        $orderPrice = new OrderPrice($user, $address);
        $priceVariables = $orderPrice->getPriceVariables($albumsCount);

        $response = [
            'album_price'   => $this->getLocalMoney($priceVariables['albums']['price']),
            'subtotal'      => $this->getLocalMoney($priceVariables['subtotal']),
            'shipping'      => $this->getLocalMoney($priceVariables['shipping']),
            'taxes'         => $this->getLocalMoney($priceVariables['taxes']),
            'total'         => $this->getLocalMoney($priceVariables['total']),
            'cod'           => $this->getLocalMoney($priceVariables['cod'])
        ];
        return response()->json(['status' => true, 'data' => $response]);
    }

    /**
     * This method is responsible for upload the images for an order
     */

    /**
        * @SWG\POST(
        *     path="/api/v2/orders/1/albums/base64",
        *     description="upload images for order with base 64",
        *     tags = {"orders"},
        *   security={{"Bearer":{}}},
        *     @SWG\Parameter(
        *         name="albums[0]",
        *         in="query",
        *         type="string",
        *         description="albums[0] array of albums",
        *         required=true,
        *     ),
        *     @SWG\Response(
        *         response=200,
        *         description="",
        *        examples={
        *     "application/json": { "status": true, "data": {} }
        *      }
        *     ),
        *     @SWG\Response(
        *         response=401,
        *         description="",
        *        examples={
        *     "application/json": { "status": false, "msg": "Unauthorized" }
        *      }
        *     )
        *  )
        */

    /**
        * @SWG\POST(
        *     path="/api/v2/orders/1/albums/multipart",
        *     description="upload images for order with multipart",
        *     tags = {"orders"},
        *   security={{"Bearer":{}}},
        *     @SWG\Parameter(
        *         name="albums[0][images][]",
        *         in="query",
        *         type="string",
        *         description="albums[0][images][] array of albums images",
        *         required=true,
        *     ),
        *     @SWG\Parameter(
        *         name="albums[0][album_name]",
        *         in="query",
        *         type="string",
        *         description="albums[0][album_name]",
        *         required=true,
        *     ),
        *     @SWG\Parameter(
        *         name="albums[0][album_count]",
        *         in="query",
        *         type="string",
        *         description="albums[0][album_count]",
        *         required=true,
        *     ),
        *     @SWG\Response(
        *         response=200,
        *         description="",
        *        examples={
        *     "application/json": { "status": true, "data": {} }
        *      }
        *     ),
        *     @SWG\Response(
        *         response=401,
        *         description="",
        *        examples={
        *     "application/json": { "status": false, "msg": "Unauthorized" }
        *      }
        *     )
        *  )
        */

    public function createAlbum($id, $image_format)
    {
        $user = auth()->user();
        $order = $user->orders()->find($id);
        if (empty($order)) {
            return response()->json(['status' => false, 'msg' => 'Order not found'], 404);
        }

        if ($order->payment_status == 'Not Paid') {
            return response()->json(['status' => false, 'msg' => 'This order has not paid yet'], 400);
        }

        $inputs = request()->only('albums');
        if ($image_format == 'base64') {
            $rules = [
                'albums' => 'required',
            ];
        } elseif ($image_format == 'multipart') {
            $rules = [
                'albums'                => 'required|array',
                'albums.*.album_name'   => 'required',
                'albums.*.album_count'  => 'required|numeric|min:1',
                'albums.*.images'       => 'required|array|min:1|max:40'
            ];
        }

        $validation = Validator::make($inputs, $rules);
        if ($validation->fails()) {
            return response()->json(['status' => false, 'msg' => $validation->errors()->first()], 400);
        }
        $albums = collect($inputs['albums']);

        $old_albums_count = $order->albums->count();

        $new_albums_count = count($albums);
        $upload_albums_count = $new_albums_count + $old_albums_count;
        if ($order->album_count < $upload_albums_count) {
            return response()->json(['status' => false, 'msg' => 'The uploaded albums is more than the paid albums'], 400);
        }

        foreach ($albums as $album) {
            if ($image_format == 'base64') {
                $album = (array)json_decode($album);
            }

            // create an album record
            $albumData = [
                'order_id'      => $order->id,
                'user_id'       => $user->id,
                'cover_id'      => 0,
                'album_name'    => $album['album_name'],
                'album_status'  => 0,
                'album_count'   => $album['album_count'],
            ];
            $createdAlbum = Album::create($albumData);

            // upload album images
            $images = $album['images'];
            foreach ($images as $image) {
                //generating unique file name;
                $file_name = str_random(10).'image_'.time().'.jpg';
                if (is_object($image)) {
                    // This is a multipart image
                    $path = Storage::putFileAs("albums/$createdAlbum->id", $image, $file_name);
                } else {
                    // This is base64 image
                    $file_data = $image;  // your base64 encoded
                    @list($type, $file_data) = explode(';', $file_data);
                    @list(, $file_data)      = explode(',', $file_data);

                    Storage::put("albums/$createdAlbum->id/$file_name", base64_decode($file_data));
                    $path = "storage/app/public/albums/$createdAlbum->id/";
                }

                $imageData = [
                    'image_path'    => $path,
                    'image_name'    => $file_name,
                    'image_size'    => Storage::size("albums/$createdAlbum->id/$file_name"),
                ];
                $createdAlbum->albumImages()->create($imageData);
            }

            // create album pdf file
            $pdfFileName = $this->createAlbumPdfFile($createdAlbum->id);
            $createdAlbum->update(['album_pdf' => $pdfFileName]);


            try {
                //convert to 1.4 version pdf
                $command = new GhostscriptConverterCommand();
                $filesystem = new Filesystem();

                $converter = new GhostscriptConverter($command, $filesystem);
                $path = "storage/albums/$createdAlbum->id/album.pdf";
                $converter->convert($path, '1.4');
            } catch (\Exception $error) {
                print($error->getMessage());
            }
        }

        $receiptFileName = $this->createReceipt($order->id);
        $order->update(['receipt_file' => $receiptFileName]);

        $order = Order::find($order->id);
        $response = new ResourcesOrder($order);
        return response()->json(['status' => true, 'msg' => 'Albums created successfully', 'data' => $response]);
    }

    private function createAlbumPdfFile($albumId)
    {
        // get the album with its images
        $album = Album::with('albumImages')->find($albumId);

        $pointToPixel = 1.333;
        $templateDimensions = [
            'width'     => round(442.20 * $pointToPixel),
            'height'    => round(300.47 *  $pointToPixel),
        ];

        $productId = 1;
        $albumId = $album->id;
        $pagesNumber = $this->createAlbumPdfPages($productId, $albumId);

        // create invitation coupon
        $coupon = $this->createInvitationCoupon($album->order_id);

        // create the album cover photo
        $coverImageName = $this->createCoverPhoto($album->id);

        $pdf = PDF::loadView('pdf.album', compact('album', 'coupon', 'coverImageName', 'pagesNumber'));
        $fileName = "album.pdf";
        Storage::put("albums/$album->id/$fileName", $pdf->output());
        return $fileName;
    }

    public function createAlbumPdfPages($productId, $albumId)
    {
        $product = Product::find($productId);
        $album = Album::with('albumImages', 'order')->find($albumId);
        $albumImages = $album->albumImages->chunk(4);

        $imagesPositions = [
            ['x' => 177,  'y' => 207],
            ['x' => 1122, 'y' => 207],
            ['x' => 177,  'y' => 1476],
            ['x' => 1122, 'y' => 1476]
        ];


        $page = 0;
        $pagesCount = count($albumImages);
        foreach ($albumImages as $images) {
            $page++;
            $template = CreateImage::make(Storage::path($product->album_template));
            
            // create user data image
            $text = "Order Info: ".$album->order->tracking_number."                                                     Customer Info: ".$album->order->user->email."                             Sheet: $page/$pagesCount";
            $userDataImage = CreateImage::canvas(79, 2655, '#fff')
                ->text($text, 30, 2540, function ($font) {
                    $font->file(public_path('fonts/Cairo-Regular.ttf'));
                    $font->size(45);
                    $font->color('#000');
                    $font->valign('top');
                    $font->angle(90);
                });

            $index = 0;
            foreach ($images as $albumImage) {
                $image = CreateImage::make(Storage::path('albums/'.$albumImage->album_id.'/'.$albumImage->image_name));
                $image->fit(886, 590);
                $image->rotate(90);
                $template->insert($image, 'top-left', $imagesPositions[$index]['x'], $imagesPositions[$index]['y']);
                $index++;
            }
            $template->insert($userDataImage, 'bottom-right', 1, 1);
            
            Storage::put("albums/$albumImage->album_id/pdf/page_$page.jpg", $template->stream('jpg'));
        }



        return $page;
    }

    private function createCoverPhoto($albumId)
    {
        $album = Album::with("user", "order")->find($albumId);
        $pixelToCM = 37.7952755906;
        $coverData = [
            'height'    => ceil(10 * $pixelToCM),
            'width'     => ceil(34.25 * $pixelToCM),
            'album_name'    => [
                'start'     => ceil(16.5 * $pixelToCM),
                'width'     => ceil(1.25 * $pixelToCM),
                'height'    => ceil(10 * $pixelToCM)
            ],
            'back'      => [
                'start' => ceil(17.75 * $pixelToCM),
            ]
        ];

        // create template image
        $template = CreateImage::canvas($coverData['width'], $coverData['height']);

        // create album name image
        $albumNameImage = CreateImage::canvas($coverData['album_name']['width'], $coverData['album_name']['height'], '#fff')
            ->text($album->album_name, 15, $coverData['album_name']['height'] - 20, function ($font) {
                $font->file(public_path('fonts/Cairo-Regular.ttf'));
                $font->size(24);
                $font->color('#000');
                $font->valign('top');
                $font->angle(90);
            });

        // create barcode image
        $barcodeString = "*".$album->order->id."*";
        $barcodeImage = CreateImage::make(DNS1D::getBarcodePNG($barcodeString, 'C39'));

        // format user info data as text
        $shippingAddress = wordwrap($album->order->shipping_address, 25, "\n", false);
        $userData = [
            $album->user->name,
            $shippingAddress,
            'Tel: ' . $album->order->shipping_phone
        ];
        $userDataText = implode(" \n", $userData);

        // set user data photo width and height
        $linesNumber = substr_count($userDataText, "\n") + 1; // + 1 to add the last line in count
        $lineHeight = 30; // the line height calculated using paint
        $coverData['user']['height'] = $barcodeImage->height() + ($linesNumber * $lineHeight) + 20;
        $coverData['user']['width'] = $barcodeImage->width() + 20; // + 20 as margins and spaces between lines

        // create user data image
        $userDataImage = CreateImage::canvas($coverData['user']['width'], $coverData['user']['height'], '#fff')
            ->text($userDataText, 10, 10, function ($font) {
                $font->file(public_path('fonts/Cairo-Regular.ttf'));
                $font->size(15);
                $font->color('#000');
                $font->valign('top');
            });




        // insert the parcode image in the user data image
        $userDataImage->insert($barcodeImage, 'bottom-left', 10, 10);

        // get cover image
        $coverImageName = Setting::where('key', 'cover_photo')->first()->value;

        $coverImage = CreateImage::make(Storage::get($coverImageName));

        // insert front image
        $template->insert($coverImage);

        // insert album name image
        $template->insert($albumNameImage, 'top-left', $coverData['album_name']['start']);

        // insert back image
        $template->insert($coverImage, 'top-left', $coverData['back']['start']);

        // insert user data image
        $template->insert($userDataImage, 'bottom-left', 10, 10);

        // save template image
        $imageName = 'album_' . $album->id . '_cover_image.jpg';
        Storage::put("albums/$album->id/$imageName", $template->stream('jpg'));
        return $imageName;
    }

    private function createInvitationCoupon($orderId)
    {
        // get invitation coupons settings from settings table
        $keys = ['invitation_coupon_value', 'invitation_coupon_usage_times', 'invitation_coupon_value_type'];
        $settings = Setting::whereIn('key', $keys)->pluck('value', 'key');

        // create the coupon only if all settings are available
        if (count($settings) == count($keys)) {
            $code = 'invite-' . $orderId . strtolower(str_random(6));
            $couponData = [
                'type'          => 'invitation',
                'value_type'    => $settings['invitation_coupon_value_type'],
                'value'         => $settings['invitation_coupon_value'],
                'usage_times'   => $settings['invitation_coupon_usage_times'],
                'code'          => $code,
            ];
            $coupon = Coupon::create($couponData);
            return $coupon;
        }
    }

    public function createReceipt($orderId)
    {
        $order = Order::with('albums')->find($orderId);
        $albums = $order->albums;
        $user = auth()->user();
        $shippingAddress = $user->addresses()->find($order->shipping_address_id);
        $orderPrice = new OrderPrice($user, $shippingAddress);
        $orderPrice->useOrder($order);
        $priceVariables = $orderPrice->getPriceVariables($order->album_count);
        $pdf = PDF::loadView('pdf.receipt', compact('order', 'albums', 'priceVariables'));
        $fileName = "receipt.pdf";
        Storage::put("receipts/$order->id/$fileName", $pdf->output());
        return $fileName;
    }
}
