<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Courier extends Model
{
    protected $fillable = [
        'user_id', 'city_id', 'fee', 'cash_delivery_primary_amount', 'cash_delivery_primary_amount_fee', 'cash_delivery_additional_amount', 'cash_delivery_additional_amount_fee','status','default'
    ];

    public function city()
    {
        return $this->belongsTo('App\Models\City', 'city_id');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }

    public function zones()
    {
        return $this->hasMany(CourierZone::class);
    }

    public function prices()
    {
        return $this->hasMany(CourierPrice::class);
    }

    public function orders(){
        return $this->hasMany(Order::class);
    }

    public function courierOrders()
    {
        return $this->hasMany('App\Models\courierOrder');
    }
}
