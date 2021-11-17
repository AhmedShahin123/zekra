<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CurrencyTranslation extends Model
{
    protected $fillable = [
  'currency_id', 'currency_name', 'locale',
];

    public function currency()
    {
        return $this->belongsTo('App\Models\Currency', 'currency_id');
    }
}
