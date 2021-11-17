<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourierZone extends Model
{
    //

    protected $fillable = [
        'courier_id', 'city_id', 'zone'
    ];

    public function city(){
        return $this->belongsTo(City::class);
    }
}
