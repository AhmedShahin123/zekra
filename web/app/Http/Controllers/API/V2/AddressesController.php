<?php

namespace App\Http\Controllers\API\V2;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;

use App\Http\Resources\Address as AddressResource;

use App\Models\City;
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
        $user = auth()->user();
        $cities_ids = City::pluck('id')->toArray();

        // set the validation rules and validate the user input
        $validator = Validator::make(request()->all(), [
            'name'      => 'required',
            'city_id'   => ['required', Rule::in($cities_ids)],
            'default'   => ['required', Rule::in([0, 1])]
        ]);

        // return an error message in case validation fails
        if ($validator->fails()) {
            return response()->json(['status' => false, 'msg' => $validator->errors()->first()], 400);
        }

        $inputs = request()->only('name', 'city_id', 'address_1', 'address_2', 'postal_code', 'phone', 'default');
        if($inputs['default'] == 0 && count($user->addresses) == 0){
            // if the user does not have any other addresses then this address should be the default address
            $inputs['default'] = 1;
        }

        $address = $user->addresses()->create($inputs);
        if($inputs['default'] == 1){
            $this->setDefaultAddress($address->id);
        }

        // if user does not have a city id then update user city and country with this address
        if($user->city_id == null){
            $user->update(['city_id' => $address->city_id, 'country_id' => $address->city->country_id, 'address' => $address->address_1]);
        }

        $response = new AddressResource($user->addresses()->find($address->id));

        // return success response
        return response()->json(['status'=>true,'msg' => 'user address created successfully', 'data' => $response], 201);
    }

    public function defaultAddress($address_id){
        $address = auth()->user()->addresses()->find($address_id);
        if(empty($address)){
            return response()->json(['status' => false, 'msg' => 'address not found'], 404);
        }

        $this->setDefaultAddress($address_id);
        $response = new AddressResource($address);
        return response()->json(['status' => true, 'msg' => 'user address updated successfully', 'data' => $response]);
    }

    private function setDefaultAddress($address_id){
        auth()->user()->addresses()->update(['default' => 0]);
        auth()->user()->addresses()->find($address_id)->update(['default' => 1]);
    }

    public function updateAddress($address_id){
        $user = auth()->user();
        $address = $user->addresses()->find($address_id);
        if(empty($address)){
            return response()->json(['status' => false, 'msg' => 'address not found'], 404);
        }

        $cities_ids = City::pluck('id')->toArray();

        // set the validation rules and validate the user input
        $validator = Validator::make(request()->all(), [
            'name'      => 'required',
            'city_id'   => ['required', Rule::in($cities_ids)],
            'default'   => ['required', Rule::in([0, 1])]
        ]);

        // return an error message in case validation fails
        if ($validator->fails()) {
            return response()->json(['status' => false, 'msg' => $validator->errors()->first()], 400);
        }

        $inputs = request()->only('name', 'city_id', 'address_1', 'address_2', 'postal_code', 'phone', 'default');
        if($inputs['default'] == 0 && count($user->addresses) <= 1){
            // if the user does not have any other addresses but this address then this address should be the default address
            $inputs['default'] = 1;
        }
        
        $address->update($inputs);
        if($inputs['default'] == 1){
            $this->setDefaultAddress($address->id);
        }
        $response = new AddressResource($address);
        return response()->json(['status' => true, 'msg' => 'user address updated successfully', 'data' => $response]);
    }

    public function deleteAddress($id)
    {
        $user = auth()->user();
        $address = $user->addresses()->find($id);
        if (empty($address)) {
            return response()->json(['status' => false, 'msg' => 'address not found'], 404);
        }

        if($address->default){
            return response()->json(['status' => false, 'msg' => 'user can not delete the default address'], 400);
        }

        $address->delete();
        return response()->json(['status' => true, 'msg' => 'address deleted successfully'], 200);
    }
}
