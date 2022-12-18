<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController as BaseController;
use App\Http\Resources\Movie as MovieResource;
use App\Models\Movie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class MoviesController extends BaseController
{
    public function index()
    {
        $movies = Movie::all();

        return $this->sendResponse(MovieResource::collection($movies), 'All Movies retrieved successfully');
    }

    public function store(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'title'         => 'required',
            //'summary'       => 'required',
            'in_theaters'   => 'required|boolean',
            'release_date'  => 'required|date|date_format:Y/m/d',
            'poster'        => 'image'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Please Validate', $validator->errors());
        }

        $newPoster = null;
        if ($request->has('poster')) {
            $poster = $request->poster;
            $newPoster = time() . $poster->getClientOriginalName();
            $poster->move('uploads/Movies-Posters', $newPoster);
            //$post->photo = 'uploads/posts/'.$newPhoto;
        }

        $photoStore = $newPoster;

        $movie = Movie::create([
            'title' => $request->title,
            'summary' => $request->has('summary') ? $request->summary : '',
            'in_theaters' => $request->in_theaters,
            'release_date' => $request->release_date,
            'poster' => $photoStore,
        ]);

        //$movie = Movie::create($input);
        return $this->sendResponse(new MovieResource($movie), 'Movie created successfully');
    }

    public function show($id)
    {
        $movie = Movie::find($id);

        if (is_null($movie)) {
            return $this->sendError('Movie not found');
        }

        return $this->sendResponse(new MovieResource($movie), 'Movie retrieved successfully');
    }

    public function update(Request $request, $id)
    {
        $input = $request->all();

        $movie = Movie::find($id);

        if (is_null($movie)) {
            return $this->sendError('Movie not found');
        }

        $validator = Validator::make($input, [
            'title'         => 'required',
            //'summary'       => 'required',
            'in_theaters'   => 'boolean',
            'release_date'  => 'date|date_format:Y/m/d',
            'poster'        => 'image'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Please Validate', $validator->errors());
        }

        $newPoster = null;
        if ($request->hasFile('poster')) {
            $poster = $request->poster;
            $newPoster = time() . $poster->getClientOriginalName();
            $poster->move(public_path('uploads/Movies-Posters/'), $newPoster);

            $old_image = public_path('uploads\\Movies-Posters\\') . $movie->poster;
            File::delete($old_image);
        }

        $photoStore = $newPoster == null ? '' : $newPoster;

        $movie->title = $input['title'];
        $movie->summary = $request->has('summary') ? $input['summary'] : $movie->summary;
        $movie->in_theaters = $request->has('in_theaters') ? $input['in_theaters'] : $movie->in_theaters;
        $movie->release_date = $request->has('release_date') ? $input['release_date'] : $movie->release_date;
        $movie->poster = $photoStore;

        $movie->save();

        return $this->sendResponse(new MovieResource($movie), 'Movie updated successfully');
    }

    public function destroy($id)
    {
        $movie = Movie::find($id);

        if (is_null($movie)) {
            return $this->sendError('Movie not found');
        }
        $old_image = public_path('uploads\\Movies-Posters\\') . $movie->poster;
        $movie->delete();
        File::delete($old_image);

        return $this->sendResponse(new MovieResource($movie), 'Movie deleted successfully');
    }
}
