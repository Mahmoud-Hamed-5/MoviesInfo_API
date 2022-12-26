<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MovieCast extends JsonResource
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
        $cast = [];
        $index = 0;
        foreach ($this->people as $person) {


            $cast[$index]['Name']  = $person->name;
            $cast[$index]['Role']  = $person->pivot->role;
            $cast[$index]['Order'] = $person->pivot->order;

            $index++;
        }

        // $genres = [];
        // $index = 0;
        // foreach ($this->genres as $genre) {


        //     $genres[$index]  = $genre->name;

        //     $index++;
        // }

        return [
            //'id'            => $this->id,
            'Movie_Title'   => $this->title,
            'Cast'          => $cast,
            //'Genres'        => $genres,
        ];
    }
}
