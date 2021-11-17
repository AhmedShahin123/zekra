<?php

namespace App\Http\Controllers\API\V2;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\User;
use App\Models\SocialIdentity;

use Socialite;
use Validator;

class SocialAuthController extends Controller
{

  /**
      * @SWG\Post(
      *     path="/api/v2/auth/facebook",
      *     description="Auth with facebook",
      *     tags = {"user"},
      *     @SWG\Parameter(
      *         name="token",
      *         in="query",
      *         type="string",
      *         description="facebook token",
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

    public function facebookAuth()
    {
        $validator = Validator::make(request()->all(), [
            'token'     => 'required'
        ]);

        // return an error message in case validation fails
        if ($validator->fails()) {
            return response()->json(['status' => false, 'msg' => $validator->errors()->first()], 400);
        }

        $token = request('token');
        try {
            $social = Socialite::driver('facebook')->userFromToken($token);

            // check if this user signed before
            $socialIdentityData = ['provider_name' => 'facebook', 'provider_id' => $social->id];
            $socialIdentity = SocialIdentity::where($socialIdentityData)->first();
            if (empty($socialIdentity)) {
                // create new user
                $userData = collect($social)->only('name', 'email', 'avatar')->toArray();

                // generate an email if returned email is null
                if ($userData['email'] == null) {
                    $email = str_replace(' ', '_', strtolower($userData['name'])).'_'.strtotime(now()).'@zekra.com';
                    $userData['email'] = $email;
                }

                $userData['password'] = bcrypt($userData['email']);


                $create = User::create($userData);
                $create->identities()->create($socialIdentityData);

                $user_id = $create->id;
            } else {
                $user_id = $socialIdentity->user_id;
            }

            $user = User::find($user_id);
            $user->token = 'Bearer '.$user->createToken('MyApp')->accessToken;

            return response()->json(['status' => true, 'msg' => 'user logged in successfully', 'data' => $user]);
        } catch (\Exception $error) {
            return response()->json(['status' => false, 'msg' => $error->getMessage()], 400);
        }
    }

    public function instagramAuth()
    {
        $validator = Validator::make(request()->all(), [
            'token'     => 'required'
        ]);

        // return an error message in case validation fails
        if ($validator->fails()) {
            return response()->json(['status' => false, 'msg' => $validator->errors()->first()], 400);
        }

        $token = request('token');
        try {
            $social = Socialite::driver('instagram')->userFromToken($token);
            // return response()->json($social);

            // check if this user signed before
            $socialIdentityData = ['provider_name' => 'instagram', 'provider_id' => $social->id];
            $socialIdentity = SocialIdentity::where($socialIdentityData)->first();
            if (empty($socialIdentity)) {
                // create new user
                $userData = collect($social)->only('name', 'email', 'avatar')->toArray();

                // generate an email if returned email is null
                if ($userData['email'] == null) {
                    $email = str_replace(' ', '_', strtolower($userData['name'])).'_'.strtotime(now()).'@zekra.com';
                    $userData['email'] = $email;
                }

                $userData['password'] = bcrypt($userData['email']);


                $create = User::create($userData);
                $create->identities()->create($socialIdentityData);

                $user_id = $create->id;
            } else {
                $user_id = $socialIdentity->user_id;
            }

            $user = User::find($user_id);
            $user->token = 'Bearer '.$user->createToken('MyApp')->accessToken;

            return response()->json(['status' => true, 'msg' => 'user logged in successfully', 'data' => $user]);
        } catch (\Exception $error) {
            return response()->json(['status' => false, 'msg' => $error->getMessage()], 400);
        }
    }
}
