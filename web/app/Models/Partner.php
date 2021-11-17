<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Partner extends Model
{
    protected $fillable = [
        'user_id', 'city_id','fee','status','default','courier_id','partner_couriers'
    ];
    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }

    public function courier()
    {
        return $this->belongsTo('App\Models\Courier', 'courier_id');
    }

    public function city()
    {
        return $this->belongsTo('App\Models\City', 'city_id');
    }

    public function orders(){
        return $this->hasMany(Order::class);
    }

    public function partnerOrders()
    {
        return $this->hasMany('App\Models\partnerOrder');
    }
}
