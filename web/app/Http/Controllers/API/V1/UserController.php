<?php

namespace App\Http\Controllers\API\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\User\UserServiceInterface;
use Illuminate\Validation\Rule;

use App\Http\Resources\User as UserResource;

use App\Models\City;
use App\Models\Country;
use App\Models\User;

use Validator;
use Hash;

class UserController extends Controller
{
    public $userService;

    public function __construct(UserServiceInterface $userService)
    {
        $this->userService = $userService;
    }

    public function changePassword()
    {

        // set the validation rules and validate the user input
        $validator = Validator::make(request()->all(), [
            'old_password'  => 'required',
            'new_password'  => 'required|min:6'
        ]);

        // return an error message in case validation fails
        if ($validator->fails()) {
            return response()->json(['status' => false, 'msg' => $validator->errors()->first()], 400);
        }

        // compare the old password with user stored password
        if (!Hash::check(request('old_password'), auth()->user()->password)) {
            return response()->json(['status' => false, 'msg' => 'The old password in incorrect'], 400);
        }

        // update the auth user password
        auth()->user()->update(['password' => bcrypt(request('new_password'))]);

        // return success respone
        return response()->json(['status'=>true,'msg' => 'user password updated successfully'], 200);
    }

    public function changeProfile()
    {
        $countries_ids = Country::pluck('id')->toArray();
        $cities_ids = City::pluck('id')->toArray();


        // set the validation rules and validate the user input
        $validator = Validator::make(request()->all(), [
            'birth_date'    => 'date',
            'gender'        => [Rule::in(['0', '1', '2'])],
            'phone'         => 'unique:users,phone,'.auth()->id()
        ]);

        // return an error message in case validation fails
        if ($validator->fails()) {
            return response()->json(['status' => false, 'msg' => $validator->errors()->first()], 400);
        }

        $inputs = request()->only('name', 'country_id', 'city_id', 'birth_date', 'gender', 'phone', 'address', 'locale');
        if (request()->has('birth_date')) {
            $inputs['birth_date'] = date('Y-m-d', strtotime($inputs['birth_date']));
        }

        // update auth user info
        auth()->user()->update($inputs);

        $user = auth()->user();
        $user->token = request()->header('Authorization');

        $response = new UserResource($user);

        // return success response
        return response()->json(['status'=>true,'msg' => 'user info updated successfully', 'data' => $response]);
    }

    public function getProfile(){
        $user = auth()->user();
        $user->token = request()->header('Authorization');
        $response = new UserResource($user);
        return response()->json(['status'=>true, 'data' => $response]);
    }
}
