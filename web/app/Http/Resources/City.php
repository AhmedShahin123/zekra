<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\ProductPrice;
use App\Traits\Helper;

class City extends JsonResource
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
        $album_price = ProductPrice::where('city_id', $this->id)->pluck('price')->first();
        return [
            'id'            => $this->id,
            'name'          => $this->city_name,
            'tax'           => $this->getLocalMoney($this->tax),
            'shipping'      => $this->getLocalMoney($this->shipping),
            'album_price'   => $this->getLocalMoney($album_price)
          ];
    }
}
