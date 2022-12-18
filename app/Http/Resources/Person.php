<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Person extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        //return parent::toArray($request);
        return [
            'id'             => $this->id,
            'name'           => $this->name,
            'biography'      => $this->biography,
            'date_of_birth'  => $this->date_of_birth,
            'picture'        => $this->picture,

            'created_at'     => $this->created_at->format('d/m/Y H:i:s A'),
            'updated_at'     => $this->updated_at->format('d/m/Y H:i:s A'),
        ];
    }
}
