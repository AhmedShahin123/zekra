<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class courierOrder extends Model
{
    protected $fillable = [
'courier_id', 'order_id','status'
];
    public function courier()
    {
        return $this->belongsTo('App\Models\Courier', 'courier_id');
    }

    public function courierOrder()
    {
        return $this->belongsTo('App\Models\Order', 'order_id');
    }
}
