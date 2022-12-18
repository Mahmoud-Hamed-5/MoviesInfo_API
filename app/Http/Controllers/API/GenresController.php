<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Genre;
use App\Http\Resources\Genre as GenreResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GenresController extends BaseController
{

    public function index()
    {
        $genres = Genre::all();

        return $this->sendResponse(GenreResource::collection($genres), 'All Genres retrieved successfully');
    }



    public function store(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'name'         => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Please Validate', $validator->errors());
        }

        $genre = Genre::create([
            'name' => $request->name
        ]);

        //$movie = Movie::create($input);
        return $this->sendResponse(new GenreResource($genre), 'Genre created successfully');
    }


    public function show($id)
    {
        $genre = Genre::find($id);

        if (is_null($genre)) {
            return $this->sendError('Genre not found');
        }

        return $this->sendResponse(new GenreResource($genre), 'Genre retrieved successfully');
    }



    public function update(Request $request, $id)
    {
        $input = $request->all();

        $genre = Genre::find($id);

        if (is_null($genre)) {
            return $this->sendError('Genre not found');
        }

        $validator = Validator::make($input, [
            'name'         => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Please Validate', $validator->errors());
        }

        $genre->name = $input['name'];

        $genre->save();

        return $this->sendResponse(new GenreResource($genre), 'Genre updated successfully');
    }


    public function destroy($id)
    {
        $genre = Genre::find($id);

        if (is_null($genre)) {
            return $this->sendError('Genre not found');
        }

        $genre->delete();

        return $this->sendResponse(new GenreResource($genre), 'Genre deleted successfully');
    }
}
