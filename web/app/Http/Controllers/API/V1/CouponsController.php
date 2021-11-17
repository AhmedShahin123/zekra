<?php

namespace App\Http\Controllers\API\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Coupon;

use App\Traits\Helper;

use App\Http\Resources\Coupon as CouponResource;

class CouponsController extends Controller
{
    use Helper;

    public function getCoupon($code){
        
        $coupon = Coupon::where('code', $code)->first();
        if(empty($coupon)){
            return response()->json(['status' => false, 'msg' => 'Coupon not found'], 404);
        }

        // validate coupon
        $validation = $this->validateCoupon($coupon);
        if($validation['failed']){
            return response()->json(['status' => false, 'msg' => $validation['message']], 400);
        }

        $response = new CouponResource($coupon);
        return response()->json(['status' => true, 'data' => $response]);
    }
}
