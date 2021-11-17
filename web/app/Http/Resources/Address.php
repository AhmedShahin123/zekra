<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Address extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'            => $this->id,
            'name'          => $this->name,
            'user'          => $this->user->name,
            'country'       => $this->city->country->country_name,
            'country_id'    => $this->city->country_id,
            'city'          => $this->city->city_name,
            'city_id'       => $this->city_id,
            'address_1'     => $this->address_1,
            'address_2'     => $this->address_2,
            'postal_code'   => $this->postal_code,
            'phone'         => $this->phone,
            'default'       => $this->default
        ];
    }
}
