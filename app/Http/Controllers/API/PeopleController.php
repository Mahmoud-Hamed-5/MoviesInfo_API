<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Person;
use Illuminate\Http\Request;
use App\Http\Resources\Person as PersonResource;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class PeopleController extends BaseController
{

    public function index()
    {
        $people = Person::all();

        return $this->sendResponse(PersonResource::collection($people), 'All People retrieved successfully');
    }


    public function store(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'name'           => 'required',
            //'biography'    => 'required',
            'date_of_birth'  => 'required|date|date_format:Y/m/d',
            'picture'         => 'image'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Please Validate', $validator->errors());
        }

        $newpicture = null;
        if ($request->has('picture')) {
            $picture = $request->picture;
            $newpicture = time() . $picture->getClientOriginalName();
            $picture->move('uploads/People-Pictures', $newpicture);
        }

        $photoStore = $newpicture;

        $person = Person::create([
            'name' => $request->name,
            'biography' => $request->has('biography') ? $request->biography : '',
            'date_of_birth' => $request->date_of_birth,
            'picture' => $newpicture,
        ]);


        return $this->sendResponse(new PersonResource($person), 'Person created successfully');
    }


    public function show($id)
    {
        $person = Person::find($id);

        if (is_null($person)) {
            return $this->sendError('Person not found');
        }

        return $this->sendResponse(new PersonResource($person), 'Person retrieved successfully');
    }


    public function update(Request $request, $id)
    {
        $input = $request->all();

        $person = Person::find($id);

        if (is_null($person)) {
            return $this->sendError('Person not found');
        }

        $validator = Validator::make($input, [
            //'name'           => 'required',
            //'biography'    => 'required',
            'date_of_birth'  => 'date|date_format:Y/m/d',
            'picture'         => 'image'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Please Validate', $validator->errors());
        }

        $newpicture = null;
        if ($request->hasFile('picture')) {
            $picture = $request->picture;
            $newpicture = time() . $picture->getClientOriginalName();
            $picture->move(public_path('uploads/People-Pictures/'), $newpicture);

            $old_image = public_path('uploads\\People-Pictures\\') . $person->picture;
            File::delete($old_image);
        }

        $photoStore = $newpicture == null ? '' : $newpicture;

        $person->name = $request->has('name') ? $input['name'] : $person->name;
        $person->biography = $request->has('biography') ? $input['biography'] : $person->biography;

        $person->date_of_birth = $request->has('date_of_birth') ? $input['date_of_birth'] : $person->date_of_birth;
        $person->picture = $photoStore;

        $person->save();

        return $this->sendResponse(new PersonResource($person), 'Person updated successfully');
    }

    public function destroy($id)
    {
        $person = Person::find($id);

        if (is_null($person)) {
            return $this->sendError('Person not found');
        }
        $old_image = public_path('uploads\\People-Pictures\\') . $person->picture;
        $person->delete();
        File::delete($old_image);

        return $this->sendResponse(new PersonResource($person), 'Person deleted successfully');
    }
}
