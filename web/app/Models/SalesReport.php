<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesReport extends Model
{
    protected $table = 'transactions';
    
    protected $fillable = ['payment_id', 'type', 'amount'];

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }
}
