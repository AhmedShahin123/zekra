<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CityTranslation extends Model
{
    protected $fillable = [
'city_id', 'city_name', 'locale',
];

    public function city()
    {
        return $this->belongsTo('App\Models\City', 'city_id');
    }
}
