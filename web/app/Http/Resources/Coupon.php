<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

use App\Traits\Helper;

class Coupon extends JsonResource
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
            'is_discount'   => $this->is_discount,
            'code'          => $this->code,
            'value_type'    => $this->value_type,
            'value'         => $this->value_type == 'money' ? $this->getLocalMoney($this->value) : $this->value,
            'usage_times'   => $this->usage_times,
            'expire_at'     => $this->expire_at,
            'active'        => $this->active,
        ];
    }
}
