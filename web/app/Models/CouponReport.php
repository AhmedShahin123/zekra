<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CouponReport extends Model
{
    protected $table = 'coupons';

    protected $fillable = ['type', 'code', 'value', 'usage_times', 'expire_at'];

    protected $casts = [
        'expire_at' => 'date'
    ];

    public function orders(){
        return $this->hasMany(Order::class, 'coupon_id');
    }
}
