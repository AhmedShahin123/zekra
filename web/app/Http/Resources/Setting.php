<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class Setting extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $value = $this->type == 'image' ? Storage::url($this->value) : $this->value;
        return [
            'id'            => $this->id,
            'name'          => $this->name,
            'key'           => $this->key,
            'value'         => $value,
            'type'          => $this->type,
            'section'       => $this->section,
            'extra_data'    => $this->extra_data
        ];
    }
}
