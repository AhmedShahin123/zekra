<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id', 
        'partner_id', 
        'courier_id', 
        'shipping_address_id',
        'shipping_address',
        'shipping_phone',
        'album_count',
        'fee',
        'tax', 
        'total', 
        'receipt_file', 
        'progress_status',
        'delivery_status',
        'progress_status_date',
        'delivery_status_date',
        'payment_status'
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }

    public function partnerOrders()
    {
        return $this->hasMany('App\Models\partnerOrder');
    }

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }

    public function courierOrders()
    {
        return $this->hasMany('App\Models\courierOrder');
    }

    public function courier()
    {
        return $this->belongsTo(Courier::class);
    }

    public function albums()
    {
        return $this->hasMany(Album::class);
    }

    public function receipts()
    {
        return $this->hasMany(Receipt::class);
    }

    public function refund()
    {
        return $this->hasOne(Refund::class);
    }

    public function payment(){
        return $this->morphOne(Payment::class, 'purchased');
    }

    public function successPayment(){
        return $this->morphOne(Payment::class, 'purchased')->where('status', 1);
    }

    public function shippingAddress(){
        return $this->belongsTo(UserAddress::class, 'shipping_address_id');
    }

    public function getRefundableAttribute(){
        return empty($this->refund);
    }

    public function getIsPaidAttribute(){
        if($this->payment_status !== 'Paid'){
            return false;
        }

        if(empty($this->successPayment)){
            return false;
        }

        return true;
    }

    public function getHasAlbumsAttribute(){
        return count($this->albums) !== 0;
    }

    public function getTrackingNumberAttribute(){
        return '#'.str_pad($this->id, 9, "0123", STR_PAD_LEFT);
    }
}
