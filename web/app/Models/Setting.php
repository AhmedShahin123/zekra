<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'name', 'key', 'value', 'type', 'section', 'extra_data'
    ];

    protected $casts = [
        'extra_data' => 'array'
    ];

}
