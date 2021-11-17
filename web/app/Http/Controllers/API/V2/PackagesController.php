<?php

namespace App\Http\Controllers\API\V2;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\Package as ResourcesPackage;
use App\Models\Package;
use App\Models\Payment;
use App\Models\User;

use App\Models\UserPackage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

use App\Traits\Payment as PaymentTrait;

class PackagesController extends Controller
{
    use PaymentTrait;
    /**
        * @SWG\GET(
        *     path="/api/v2/packages",
        *     description="get all packages",
        *     tags = {"packages"},
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

    public function getPackages()
    {
        $packages = Package::all();
        $response = ResourcesPackage::collection($packages);
        return response()->json(['status' => true, 'data' => $response]);
    }

    /**
        * @SWG\Post(
        *     path="/api/v2/packages/1/purchase",
        *     description="purchase a package by id",
        *     tags = {"packages"},
        *   security={{"Bearer":{}}},
        *     @SWG\Parameter(
        *         name="card_token",
        *         in="query",
        *         type="string",
        *         description="card_token(tok_visa)",
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

    public function purchasePackage($id)
    {
        $package = Package::find($id);

        /**
         * Validate the package
         * 1. If this package exists
         * 2. If this package not expired
         * 3. If this package has room for another user
         */

        // If this package exists
        if (empty($package)) {
            return response()->json(['status' => false, 'msg' => 'This package not found'], 404);
        }

        // If this package not expired
        $expire_date = Carbon::parse($package->expire_at);
        $today = Carbon::now();
        if ($expire_date->lessThanOrEqualTo($today)) {
            return response()->json(['status' => false, 'msg' => 'This package has expired'], 400);
        }

        // If this package has room for another user
        if ($package->users->count() >= $package->max_users) {
            return response()->json(['status' => false, 'msg' => 'This package has exceeded its users limit'], 400);
        }

        $validation = Validator::make(request()->all(), ['card_token' => 'required']);
        if ($validation->fails()) {
            return response()->json(['status' => false, 'msg' => $validation->errors()->first()], 400);
        }

        // charge the user for the price of the package using strip
        $cardToken = request('card_token');
        $payment = $this->handlePayment($cardToken, $package->price);
        if ($payment['failed']) {
            return response()->json(['status' => false, 'msg' => $payment['msg'], 'data' => $payment['data']], $payment['status_code']);
        }

        // store user package data
        $userPackageData = [
            'user_id'               => auth()->id(),
            'package_id'            => $package->id,
            'package_credit_points' => $package->credit_points
        ];
        UserPackage::create($userPackageData);

        // store the transaction data in payments table
        $paymentData  = [
            'user_id'               => auth()->id(),
            'purchased_type'        => Package::class,
            'purchased_id'          => $package->id,
            'payment_method'        => 'credit_card',
            'money_amount'          => $package->price,
            'points_amount'         => 0,
            'card_token'            => $cardToken,
            'payment_provider'      => 'stripe',
            'payment_provider_id'   => $payment['data']->id,
            'status'                => 1,
        ];
        $payment = Payment::create($paymentData);

        $user = User::where('id', auth()->id())->first();
        try {
            $user->sendCreditNotification($package);
        } catch (\Exception $e) {
            return response()->json(['status' => true, 'msg' => 'Credit mail not sent successfully']);
        }


        $response = new ResourcesPackage($package);
        return response()->json(['status' => true, 'msg' => 'Package purchased successfully', 'data' => $response]);
    }
}
