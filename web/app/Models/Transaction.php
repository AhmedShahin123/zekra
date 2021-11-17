<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = ['payment_id', 'type', 'amount'];

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }
}
