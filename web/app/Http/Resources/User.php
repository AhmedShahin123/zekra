<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class User extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $avatar = $this->avatar == null ? 'users/default_user' : $this->avatar;
        return [
            'id'            => $this->id,
            'name'          => $this->name,
            'email'         => $this->email,
            'phone'         => $this->phone,
            'avatar'        => Storage::url($avatar),
            'city_id'       => $this->city_id,
            'city_name'     => $this->city ? $this->city->city_name : null,
            'country_id'    => $this->city ? $this->city->country_id : null,
            'country_name'  => $this->city ? $this->city->country->country_name : null,
            'address'       => $this->address,
            'birth_date'    => $this->birth_date,
            'gender'        => $this->gender,
            'gender_name'   => $this->getGenderName($this->gender),
            'locale'        => $this->locale ? $this->locale : 'en',
            'status'        => $this->status,
            'token'         => $this->token,
            'has_card'      => count($this->cards) !== 0,
            'credit_points' => $this->credit_points
        ];
    }
}
