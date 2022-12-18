<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Movie extends JsonResource
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
            'id'            => $this->id,
            'title'         => $this->title,
            'summary'       => $this->summary,
            'in_theaters'   => $this->in_theaters == 1 ? 'true' : 'false',
            'release_date'  => $this->release_date,
            'poster'        => $this->poster,
            'created_at'    => $this->created_at->format('d/m/Y H:i:s A'),
            'updated_at'    => $this->updated_at->format('d/m/Y H:i:s A'),
        ];
    }
}
