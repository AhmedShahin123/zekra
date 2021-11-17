<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = ['user_id', 'purchased_type', 'purchased_id', 'payment_method', 'money_amount', 'points_amount', 'card_token', 'payment_provider', 'payment_provider_id', 'status', 'extra_data'];

    protected $casts = [
        'status'        => 'boolean',
        'extra_data'    => 'array'
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function transactions(){
        return $this->hasMany(Transaction::class);
    }

    public function zekraPaymentsTransaction(){
        return $this->hasOne(Transaction::class)->where('type', 'zekra_payments');
    }

    public function purchased(){
        return $this->morphTo();
    }
}
