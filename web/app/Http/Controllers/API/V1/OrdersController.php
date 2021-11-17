<?php

namespace App\Http\Controllers\API\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Order\OrderServiceInterface;
use App\Models\Album;
use App\Models\Image;
use App\Models\partnerOrder;
use App\Models\Partner;
use App\Models\courierOrder;
use App\Models\Courier;
use App\Models\CourierPrice;
use App\Models\Order;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Receipt;

use App\Models\Setting;
use \Milon\Barcode\DNS1D;
use Carbon\Carbon;
use App;
use App\Http\Resources\Order as ResourcesOrder;
use App\Models\Coupon;
use App\Models\Payment;
use App\Traits\Helper;
use App\Traits\Payment as PaymentTrait;
use Stripe;
use Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Image as CreateImage;
use PDF;

class OrdersController extends Controller
{
    use Helper;
    use PaymentTrait;

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

    public function createReceipt($orderId)
    {
        // get the order
        $order = Order::find($orderId);
        $albums = Album::where('order_id', $order->id)->get();
        $albumPrice = Setting::where('key', 'album_price')->pluck('value')->first();
        $albumTotal =  $albums->count()* $albumPrice;
        $tax = $albumTotal * $order->user->city->tax / 100;
        //dd($tax);
        $total = $tax + $albumTotal;
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
            ],
            'user'      => [
                'height'    => ceil(4 * $pixelToCM),
            ]
        ];
        //dd($coverData['width']);

        $userData = [
            $order->user->name,
            $order->shipping_address,
            'Tel: ' . $order->shipping_phone
        ];
        $userDataText = implode(" \n", $userData);

        // create user data image
        $userDataImage = CreateImage::canvas($coverData['width'], $coverData['height'], '#fff')
            ->text($userDataText, 10, 10, function ($font) {
                $font->file(public_path('fonts/Cairo-Regular.ttf'));
                $font->size(15);
                $font->color('#000');
                $font->valign('top');
            });



        $pdf = PDF::loadView('pdf.receipt', compact('total', 'tax', 'albumTotal', 'albumPrice', 'albums', 'order', 'userDataImage'));
        $fileName = "receipt.pdf";
        Storage::put("receipts/$order->id/$fileName", $pdf->output());
        return $fileName;
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

    private function createAlbumPdfFile($albumId)
    {
        // get the album with its images
        $album = Album::with('albumImages')->find($albumId);

        // create invitation coupon
        $coupon = $this->createInvitationCoupon($album->order_id);

        // create the album cover photo
        $coverImageName = $this->createCoverPhoto($album->id);

        $pdf = PDF::loadView('pdf.v1.album', compact('album', 'coupon', 'coverImageName'));
        $fileName = "album.pdf";
        Storage::put("albums/$album->id/$fileName", $pdf->output());
        return $fileName;
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
            ],
            'user'      => [
                'height'    => ceil(4 * $pixelToCM),
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
        // set user data photo width
        $coverData['user']['width'] = $barcodeImage->width() + 20;

        // format user info data as text
        $userData = [
            $album->user->name,
            $album->order->shipping_address,
            'Tel: ' . $album->order->shipping_phone
        ];
        $userDataText = implode(" \n", $userData);

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

    public function myOrders()
    {
        $userOrders = $this->orderService->userOrders(auth()->id());
        if ($userOrders) {
            $response = ResourcesOrder::collection($userOrders);
            return response()->json(['status' => true, 'data' => $response], 200);
        }
        return response()->json(['status' => false, 'msg' => 'there is no orders'], 401);
    }

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


    public function checkoutOrder($id)
    {
        $order = auth()->user()->orders()->find($id);

        // check if this order exists
        if (empty($order)) {
            return response()->json(['status' => false, 'msg' => 'Order not found'], 404);
        }

        // check if this order has not paid yet
        // if($order->payment_status == 'Paid'){
        //     return response()->json(['status' => false, 'msg' => 'This order has already paid'], 400);
        // }

        // validate order paying method
        $validation = Validator::make(request()->all(), [
            'payment_method'    => ['required', Rule::in(['credit_card', 'credit_points'])],
            'card_token'        => 'required_if:payment_method,credit_card'
        ]);
        if ($validation->fails()) {
            return response()->json(['status' => false, 'msg' => $validation->errors()->first()], 400);
        }

        $amount = $order->total;
        // check if user has a discount coupon
        if (request()->has('discount_code')) {
            $discountCode = request('discount_code');
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

            $orderDiscountData = [
                'coupon_id'         => $coupon->id,
                'discount_value'    => $coupon->value,
                'discount_code'     => $coupon->code
            ];
            // update order with discount data
            $order->update($orderDiscountData);

            $amount -= $coupon->value;
        }

        // charge user for his order
        if (request()->get('payment_method') == 'credit_card') { // stripe flow
            $cardToken = request('card_token');
            $payment = $this->handlePayment($cardToken, $amount);
            if ($payment['failed']) {
                return response()->json(['status' => false, 'msg' => $payment['msg'], 'data' => $payment['data']], $payment['status_code']);
            }

            // collect the payments data
            $paymentData  = [
                'payment_method'        => 'credit_card',
                'amount'                => $amount,
                'card_token'            => $cardToken,
                'payment_provider'      => 'stripe',
                'payment_provider_id'   => $payment['data']->id
            ];
        } elseif (request()->get('payment_method') == 'credit_points') { // credit points flow
            // check if the user has enough credit
            $orderPriceInCreditPoints = $order->albums->count();
            if (auth()->user()->credit_points < $orderPriceInCreditPoints) {
                return response()->json(['status' => false, 'msg' => 'No enough credit points'], 400);
            }

            // collect the payments data
            $paymentData  = [
                'payment_method'        => 'credit_points',
                'amount'                => $orderPriceInCreditPoints,
            ];
        }

        // collect the payments data
        $paymentData['user_id']         = auth()->id();
        $paymentData['purchased_type']  = Order::class;
        $paymentData['purchased_id']    = $order->id;

        // store payments data
        Payment::create($paymentData);

        $response = new ResourcesOrder($order);
        return response()->json(['status' => true, 'msg' => 'Order purchased successfully', 'data' => $response]);
    }

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
}
