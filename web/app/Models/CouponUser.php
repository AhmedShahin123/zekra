<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CouponUser extends Model
{
    protected $table = 'coupon_user';

    protected $fillable = ['coupon_id', 'user_id', 'order_id', 'coupon_code', 'value_type', 'value'];
}
