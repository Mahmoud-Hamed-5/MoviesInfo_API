<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController as BaseController;
use App\Http\Resources\Movie as MovieResource;
use App\Http\Resources\MovieCast as MovieCastResource;
use App\Models\Genre;
use App\Models\Movie;
use App\Models\MovieCast;
use App\Models\Person;
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
            'genres'   => 'array',
            //'summary'       => 'required',
            'in_theaters'   => 'required|boolean',
            'release_date'  => 'required|date|date_format:Y/m/d',
            'poster'        => 'image'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Please Validate', $validator->errors());
        }

        // Validate the input genres if exists
        $is_genre_exist = true;
        $genres_errors = [];
        foreach ($request->genres as  $genre_id) {
            $genre = Genre::find($genre_id);

            if (is_null($genre)) {
                $is_genre_exist = false;
                $genres_errors[$genre_id] = 'genre not found';
            }
        }
        if (!$is_genre_exist) {
            return $this->sendError($genres_errors);
        }

        // check the input for image , if exists store the image and save its name in database
        $newPoster = null;
        if ($request->has('poster')) {
            $poster = $request->poster;
            $newPoster = time() . $poster->getClientOriginalName();
            $poster->move('uploads/Movies-Posters', $newPoster);
        }
        $photoStore = $newPoster == null ? '' : $newPoster;

        // create a new movie with the input data
        $movie = Movie::create([
            'title' => $request->title,
            'summary' => $request->has('summary') ? $request->summary : '',
            'in_theaters' => $request->in_theaters,
            'release_date' => $request->release_date,
            'poster' => $photoStore,
        ]);

        // Attach the movie to genres .. store movie_id and genre_id in movies_genres table
        //      ** see relation in Movie Model **
        $movie->genres()->attach($request->genres);

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
            //'title'         => 'required',
            'genres' => 'array',
            //'summary'       => 'required',
            'in_theaters'   => 'boolean',
            'release_date'  => 'date|date_format:Y/m/d',
            'poster'        => 'image'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Please Validate', $validator->errors());
        }

        // Validate the input genres if exists
        $is_genre_exist = true;
        $genres_errors = [];
        foreach ($request->genres as  $genre_id) {
            $genre = Genre::find($genre_id);

            if (is_null($genre)) {
                $is_genre_exist = false;
                $genres_errors[$genre_id] = 'genre not found';
            }
        }
        if (!$is_genre_exist) {
            return $this->sendError($genres_errors);
        }

        // Check the input for image , if exists store the image and save its name in database
        // Then Delete the old image file
        $newPoster = null;
        if ($request->hasFile('poster')) {
            $poster = $request->poster;
            $newPoster = time() . $poster->getClientOriginalName();
            $poster->move(public_path('uploads/Movies-Posters/'), $newPoster);

            $old_image = public_path('uploads\\Movies-Posters\\') . $movie->poster;
            File::delete($old_image);
        }
        $photoStore = $newPoster == null ? '' : $newPoster;

        // Update the movie data if there is new data in the request
        $movie->title = $request->has('title') ? $input['title'] : $movie->title;
        $movie->summary = $request->has('summary') ? $input['summary'] : $movie->summary;
        $movie->in_theaters = $request->has('in_theaters') ? $input['in_theaters'] : $movie->in_theaters;
        $movie->release_date = $request->has('release_date') ? $input['release_date'] : $movie->release_date;
        $movie->poster = $photoStore;

        $movie->save();


        // Attach the movie to genres .. store movie_id and genre_id in movies_genres table
        //      ** see relation in Movie Model **
        $movie->genres()->sync($request->genres);

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

        $movie->genres()->detach();
        $movie->casts()->detach();
        return $this->sendResponse(new MovieResource($movie), 'Movie deleted successfully');
    }



    public function updateMovieCast(Request $request, $id)
    {
        $input = $request->all();

        $movie = Movie::find($id);

        if (is_null($movie)) {
            return $this->sendError('Movie not found');
        }

        $validator = Validator::make($input, [
            'casts' => 'array',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Please Validate', $validator->errors());
        }

        // Validate the input genres if exists
        $is_person_exist = true;
        $people_errors = [];
        foreach ($request->casts as $cast) {
            $person = Person::find($cast['person_id']);

            if (is_null($person)) {
                $is_person_exist = false;
                $people_errors[$cast['person_id']] = 'Person not found';
            }
        }
        if (!$is_person_exist) {
            return $this->sendError($people_errors);
        }

        // Attach the movie to casts .. store movie_id and person_id in movies_casts table
        //      ** see relation in Movie Model **


        foreach ($request->casts as $cast) {
            $row = $movie->people()->where('movie_id', $id)->where('person_id', $cast['person_id'])->first();

            if (is_null($row)) {
                $movie->people()->attach($cast['person_id'], ['role'  => $cast['role'], 'order' => $cast['order']]);
            } else {
                $row->pivot->role = $cast['role'];
                $row->pivot->order = $cast['order'];
                $row->pivot->save();
            }
        }

        return $this->sendResponse(new MovieCastResource($movie), 'Movie Casts updated successfully');
    }


    public function deleteFromMovieCast(Request $request, $id)
    {
        $input = $request->all();

        $movie = Movie::find($id);

        if (is_null($movie)) {
            return $this->sendError('Movie not found');
        }

        $validator = Validator::make($input, [
            'casts' => 'array',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Please Validate', $validator->errors());
        }

        foreach ($request->casts as $cast) {
            $row = $movie->people()->where('movie_id', $id)->where('person_id', $cast['person_id'])->first();

            if (!is_null($row)) {
                $movie->people()->detach($cast['person_id']);
            }
        }

        return $this->sendResponse(new MovieResource($movie), 'Movie updated successfully');
    }
}
