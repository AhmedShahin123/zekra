<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Receipt extends Model
{
    protected $fillable = [
      'order_id', 'receipt',
  ];

    public function order()
    {
        return $this->belongsTo('App\Models\Order', 'order_id');
    }
}
