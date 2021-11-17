<?php

namespace App\Models;

// use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
// use Astrotomic\Translatable\Translatable;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model /*implements TranslatableContract*/
{
    // use Translatable;

    // public $translatedAttributes = ['currency_name','locale'];

    protected $fillable = [
      'code', 'name', 'symbol', 'value_to_dollar'
    ];
}
