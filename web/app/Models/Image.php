<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    protected $fillable = [
        'album_id', 'image_path', 'image_name','image_size',
    ];

    public function album()
    {
        return $this->belongsTo('App\Models\Album', 'album_id');
    }
}
