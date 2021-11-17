<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

use App\Traits\Helper;

class Package extends JsonResource
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
            'id'            => $this->id,
            'price'         => $this->getLocalMoney($this->price),
            'credit_points' => $this->credit_points,
            'max_users'     => $this->max_users,
            'expire_at'     => $this->expire_at
        ];
    }
}
