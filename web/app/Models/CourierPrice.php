<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourierPrice extends Model
{
    //

    protected $fillable = [
        'courier_id', 'zone', 'primary_weight', 'primary_weight_price', 'additional_weight', 'additional_weight_price'
    ];
}
