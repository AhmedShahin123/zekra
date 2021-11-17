<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    protected $fillable = ['price', 'credit_points', 'max_users', 'expire_at'];

    protected $casts = [
        'expire_at' => 'date'
    ];

    public function users(){
        return $this->belongsToMany(User::class, 'user_packages');
    }

    public function payments(){
        return $this->morphMany(Payment::class, 'purchased');
    }
}
