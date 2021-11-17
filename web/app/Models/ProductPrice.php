<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductPrice extends Model
{
    protected $fillable = [
        'country_id', 'city_id', 'product_id','price'
    ];

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
