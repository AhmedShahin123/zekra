<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Album extends Model
{
    protected $fillable = [
      'user_id', 'cover_id', 'album_name','album_status','album_pdf','order_id','album_count'
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }

    public function order()
    {
        return $this->belongsTo('App\Models\Order', 'order_id');
    }

    public function cartDetails()
    {
        return $this->hasMany('App\Models\CartDetail');
    }

    public function albumImages()
    {
        return $this->hasMany('App\Models\Image');
    }
}
