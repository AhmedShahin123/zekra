<?php

namespace App\Http\Controllers\API\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;

use App\Http\Resources\Address as AddressResource;

use App\Models\City;
use Auth;
use Validator;

class AddressesController extends Controller
{
    public function getAddresses()
    {
        $addresses = auth()->user()->addresses()->get();
        $response = AddressResource::collection($addresses);
        return response()->json(['status' => true,'data' => $response], 200);
    }

    public function createAddress()
    {
        $cities_ids = City::pluck('id')->toArray();

        // set the validation rules and validate the user input
        $validator = Validator::make(request()->all(), [
            'city_id'   => ['required', Rule::in($cities_ids)]
        ]);

        // return an error message in case validation fails
        if ($validator->fails()) {
            return response()->json(['status' => false, 'msg' => $validator->errors()->first()], 400);
        }

        $inputs = request()->only('city_id', 'address_1', 'address_2', 'postal_code', 'phone');

        // update auth user info
        $address = auth()->user()->addresses()->create($inputs);

        $response = auth()->user()->addresses()->find($address->id);
        $user = Auth::user();
        $user->city_id = request('city_id');
        $user->save();

        // return success response
        return response()->json(['status'=>true,'msg' => 'user address created successfully', 'data' => $response], 201);
    }

    public function deleteAddress($id)
    {
        $address = auth()->user()->addresses()->find($id);
        if (empty($address)) {
            return response()->json(['status' => false, 'msg' => 'address not found'], 404);
        }

        $address->delete();
        return response()->json(['status' => true, 'msg' => 'address deleted successfully'], 200);
    }
}
