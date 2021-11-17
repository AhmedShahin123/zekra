<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'weight','dimensions','album_template','status',
    ];

    public function prices(){
        return $this->hasMany(ProductPrice::class);
    }
}
