<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = ['type', 'code', 'value_type', 'value', 'usage_times', 'expire_at', 'active'];

    protected $casts = [
        'expire_at' => 'date',
        'active'    => 'boolean'
    ];

    public function orders(){
        return $this->belongsToMany(Order::class, 'coupon_user');
    }

    public function users(){
        return $this->belongsToMany(User::class);
    }

    public function getIsDiscountAttribute(){
        return $this->value_type == 'money';
    }
}
