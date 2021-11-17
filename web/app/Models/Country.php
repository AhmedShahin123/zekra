<?php

namespace App\Models;

use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;

use Illuminate\Database\Eloquent\Model;

class Country extends Model implements TranslatableContract
{
    use Translatable;

    public $translatedAttributes = ['country_name','locale'];

    protected $fillable = [
        'status', 'code', 'currency'
    ];

    public function cities()
    {
        return $this->hasMany('App\Models\City');
    }

    public function country_translation(){
        return $this->hasOne(CountryTranslation::class)->where('locale', 'en');
    }
}
