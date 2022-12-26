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
        $cast = [];
        $index = 0;
        foreach ($this->people as $person) {



            $cast[$index]['Name']  = $person->name;
            $cast[$index]['Role']  = $person->pivot->role;
            $cast[$index]['Order'] = $person->pivot->order;

            $index++;
        }

        $genres = [];
        $index = 0;
        foreach ($this->genres as $genre) {


            $genres[$index]  = $genre->name;

            $index++;
        }

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
            'Genres'        => $genres,
            'Cast'          => $cast,
        ];
    }
}
