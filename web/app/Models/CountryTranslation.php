<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CountryTranslation extends Model
{
    protected $fillable = [
        'country_id', 'country_name', 'locale',
    ];

    public function country()
    {
        return $this->belongsTo('App\Models\Country', 'country_id');
    }
}
