<?php

namespace App\Http\Controllers\API\V2;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Transaction;

use App\Traits\Helper;

use Carbon\Carbon;
use Stripe;

class StripePaymentController extends Controller
{
    use Helper;

    public function stripePost(Request $request)
    {
        $order = Order::find(request('order_id'));
        if ($order->total != request('amount')) {
            return response()->json(['status'=>false,'msg' => 'This is not amount of order'], 400);
        }


        $amount = $order->total;
        if ($request->has('discount_code')) {
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
              "source" => $request->stripeToken,
              "description" => "Request Order"
        ]);

        if ($order) {
            $mytime = Carbon::now();
            $orderData['payment_status']            = "Paid";
            $orderData['card_token']                = $payment['payment_method'];
            $orderData['stripe_transaction_date']   = $mytime->toDateTimeString();
            $order->update($orderData);

            $transaction = Transaction::where('order_id', $order->id)->first();
            $transaction->status = $order->payment_status;
            $transaction->save();

            return response()->json(['status'=>true,'msg' => 'stripe charge successfully', 'data' => $payment], 200);
        }

        return response()->json(['status'=>false,'msg' => 'order not found'], 200);
    }
}
