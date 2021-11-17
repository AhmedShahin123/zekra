<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

use App\Traits\Helper;

class Order extends JsonResource
{
    use Helper;

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'                        => $this->id,
            'user_id'                   => $this->user_id,
            'shipping_address_id'       => $this->shipping_address_id,
            'shipping_address'          => $this->shipping_address,
            'shipping_phone'            => $this->shipping_phone,
            'fee'                       => $this->getLocalMoney($this->fee),
            'tax'                       => $this->getLocalMoney($this->tax),
            'total'                     => $this->getLocalMoney($this->total),
            'progress_status'           => $this->progress_status,
            'delivery_status'           => $this->delivery_status,
            'progress_status_date'      => $this->progress_status_date,
            'delivery_status_date'      => $this->delivery_status_date,
            'tracking_number'           => $this->tracking_number,
            'payment_status'            => $this->payment_status,
            'album_count'               => $this->album_count,
            'refundable'                => $this->refundable,
            'partner'                   => $this->partner,
            'courier'                   => $this->courier,
            'is_paid'                   => $this->is_paid,
            'has_albums'                => $this->has_albums
        ];
    }
}
