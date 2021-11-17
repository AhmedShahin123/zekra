<?php

namespace App\Models;

use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;

use Illuminate\Database\Eloquent\Model;

class City extends Model implements TranslatableContract
{
    use Translatable;

    public $translatedAttributes = ['city_name','locale'];

    protected $fillable = [
        'status','country_id','tax','shipping'
    ];

    public function couriers()
    {
        return $this->hasMany('App\Models\Courier');
    }

    public function partners()
    {
        return $this->hasMany('App\Models\Partner');
    }

    public function country()
    {
        return $this->belongsTo('App\Models\Country', 'country_id');
    }

    public function city_translation()
    {
        return $this->hasOne(CityTranslation::class)->where('locale', 'en');
    }
}
