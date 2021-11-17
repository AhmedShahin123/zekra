<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Refund extends Model
{
    protected $fillable = ['order_id', 'response', 'responded_at', 'refunded_credit_points'];

    protected $casts = [
        'responded_at'  => 'date'
    ];


    public function order(){
        return $this->belongsTo(Order::class);
    }
}
