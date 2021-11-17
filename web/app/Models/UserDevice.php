<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserDevice extends Model
{
    protected $fillable = [
        'user_id', 'platform', 'device_token', 'ip_address'
    ];
}
