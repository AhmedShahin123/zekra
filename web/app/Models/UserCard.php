<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserCard extends Model
{
    protected $fillable = ['user_id', 'card_token', 'exp_month', 'exp_year', 'last4', 'default', 'brand'];

    protected $casts = [
        'default'   => 'boolean'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    
}
