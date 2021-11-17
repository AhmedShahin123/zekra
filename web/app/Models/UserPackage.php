<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPackage extends Model
{
    protected $table = 'user_packages';
    
    protected $fillable = ['user_id', 'package_id', 'package_credit_points'];
}
