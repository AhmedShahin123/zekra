<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAddress extends Model
{
    protected $fillable = ['name', 'user_id', 'city_id', 'address_1', 'address_2', 'postal_code', 'phone', 'default'];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function city(){
        return $this->belongsTo(City::class);
    }

    protected $casts = [
        'default'   => 'boolean'
    ];

    public function getLongAddress(){
        $countryName = $this->city->country->country_name;
        $cityName = $this->city->city_name;
        $address_1 = $this->address_1;
        $address_2 = $this->address_2;

        $longAddress = $countryName .' - '.$cityName;
        $longAddress .= $address_1 ? ' - '.$address_1 : '';
        $longAddress .= $address_2 ? ' - '.$address_2 : '';
        return $longAddress;
    }
}
