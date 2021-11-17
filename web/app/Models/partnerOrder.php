<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class partnerOrder extends Model
{
    protected $fillable = [
        'partner_id', 'order_id','status'
    ];

    public function partner()
    {
        return $this->belongsTo('App\Models\Partner', 'partner_id');
    }

    public function partnerOrder()
    {
        return $this->belongsTo('App\Models\Order', 'order_id');
    }
}
