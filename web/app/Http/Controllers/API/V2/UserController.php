<?php

namespace App\Http\Controllers\API\V2;

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

    /**
        * @SWG\PUT(
        *     path="/api/v2/user/changePassword",
        *     description="change user password",
        *     tags = {"user"},
        *   security={{"Bearer":{}}},
        *     @SWG\Parameter(
        *         name="old_password",
        *         in="query",
        *         type="string",
        *         description="user old_password",
        *         required=true,
        *     ),
        *     @SWG\Parameter(
        *         name="new_password",
        *         in="query",
        *         type="string",
        *         description="new_password",
        *         required=true,
        *     ),
        *     @SWG\Response(
        *         response=200,
        *         description="",
        *        examples={
        *     "application/json": { "status": true, "data": {} }
        *      }
        *     ),
        *     @SWG\Response(
        *         response=401,
        *         description="",
        *        examples={
        *     "application/json": { "status": false, "msg": "Unauthorized" }
        *      }
        *     )
        *  )
        */

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

    /**
        * @SWG\PUT(
        *     path="/api/v2/user/profile",
        *     description="update user info",
        *     tags = {"user"},
        *   security={{"Bearer":{}}},
        *     @SWG\Parameter(
        *         name="name",
        *         in="query",
        *         type="string",
        *         description="user name",
        *         required=true,
        *     ),
        *     @SWG\Parameter(
        *         name="country_id",
        *         in="query",
        *         type="integer",
        *         description="country_id",
        *         required=true,
        *     ),
        *     @SWG\Parameter(
        *         name="address",
        *         in="query",
        *         type="string",
        *         description="user address",
        *         required=true,
        *     ),
        *     @SWG\Parameter(
        *         name="phone",
        *         in="query",
        *         type="string",
        *         description="user phone",
        *         required=true,
        *     ),
        *     @SWG\Parameter(
        *         name="city_id",
        *         in="query",
        *         type="integer",
        *         description="user city_id",
        *         required=true,
        *     ),
        *     @SWG\Parameter(
        *         name="birth_date",
        *         in="query",
        *         type="string",
        *         description="birth_date",
        *         required=false,
        *     ),
        *     @SWG\Parameter(
        *         name="gender",
        *         in="query",
        *         type="string",
        *         description="gender",
        *         required=false,
        *     ),
        *     @SWG\Parameter(
        *         name="locale",
        *         in="query",
        *         type="string",
        *         description="locale",
        *         required=false,
        *     ),
        *     @SWG\Response(
        *         response=200,
        *         description="",
        *        examples={
        *     "application/json": { "status": true, "data": {} }
        *      }
        *     ),
        *     @SWG\Response(
        *         response=401,
        *         description="",
        *        examples={
        *     "application/json": { "status": false, "msg": "Unauthorized" }
        *      }
        *     )
        *  )
        */

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

    /**
        * @SWG\GET(
        *     path="/api/v2/user/getProfile",
        *     description="update user info",
        *     tags = {"user"},
        *   security={{"Bearer":{}}},
        *     @SWG\Response(
        *         response=200,
        *         description="",
        *        examples={
        *     "application/json": { "status": true, "data": {} }
        *      }
        *     ),
        *     @SWG\Response(
        *         response=401,
        *         description="",
        *        examples={
        *     "application/json": { "status": false, "msg": "Unauthorized" }
        *      }
        *     )
        *  )
        */

    public function getProfile()
    {
        $user = auth()->user();
        $user->token = request()->header('Authorization');
        $response = new UserResource($user);
        return response()->json(['status'=>true, 'data' => $response]);
    }
}
