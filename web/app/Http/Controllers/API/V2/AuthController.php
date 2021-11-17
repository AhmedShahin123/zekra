<?php

namespace App\Http\Controllers\API\V2;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\User\UserServiceInterface;
use App\Models\User;
use App\Http\Resources\User as UserResource;
use App\Models\City;
use App\Models\UserAddress;
use Illuminate\Validation\Rule;
use Socialite;
use Validator;

/**
 * @SWG\Swagger(
 *     basePath="",
 *     schemes={"https"},
 *     host=L5_SWAGGER_CONST_HOST,
 *     @SWG\SecurityScheme(
 *         securityDefinition="Bearer",
 *         type="apiKey",
 *         name="Authorization",
 *         in="header"
 *     ),
 *     @SWG\Info(
 *         version="1.0.0",
 *         title="Zekra API Documentation Swagger",
 *         description="Zekra API Documentation",
 *         @SWG\Contact(
 *             email="info@zekra.com"
 *         ),
 *     )
 * )
 */

class AuthController extends Controller
{
    public $userService;

    public function __construct(UserServiceInterface $userService)
    {
        $this->userService = $userService;
    }

    /**
        * @SWG\Post(
        *     path="/api/v2/register",
        *     description="Register user",
        *     tags = {"user"},
        *     @SWG\Parameter(
        *         name="name",
        *         in="query",
        *         type="string",
        *         description="user name",
        *         required=true,
        *     ),
        *     @SWG\Parameter(
        *         name="email",
        *         in="query",
        *         type="string",
        *         description="user email",
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
        *         name="password",
        *         in="query",
        *         type="string",
        *         description="user password",
        *         required=true,
        *     ),
        *     @SWG\Parameter(
        *         name="platform",
        *         in="query",
        *         type="string",
        *         description="platform",
        *         required=false,
        *     ),
        *     @SWG\Parameter(
        *         name="device_token",
        *         in="query",
        *         type="string",
        *         description="device_token",
        *         required=false,
        *     ),
        *     @SWG\Parameter(
        *         name="ip_address",
        *         in="query",
        *         type="string",
        *         description="ip_address",
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


    public function register()
    {
        $validator = Validator::make(request()->all(), [
            'name'      => 'required',
            'email'     => 'required|unique:users',
            'password'  => 'required|min:6',
            'phone'     => 'required|unique:users'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'msg' => $validator->errors()->first()], 400);
        }

        $inputs = request()->only('name', 'phone', 'email', 'password');
        $registeredUser = $this->userService->registerUser($inputs);

        if (request()->has('platform')) {
            // save user platform data
            $data = request()->only('platform', 'device_token', 'ip_address');
            $registeredUser->devices()->create($data);
        }

        $response = new UserResource($registeredUser);
        if ($registeredUser) {
            return response()->json(['status'=>true,'msg' => 'sign up successfully', 'data' => $response], 200);
        }
    }

    /**
        * @SWG\Post(
        *     path="/api/v2/login",
        *     description="Log in",
        *     tags = {"user"},
        *     @SWG\Parameter(
        *         name="email",
        *         in="query",
        *         type="string",
        *         description="user email",
        *         required=true,
        *     ),
        *     @SWG\Parameter(
        *         name="password",
        *         in="query",
        *         type="string",
        *         description="user password",
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

    public function login()
    {
        $validator = Validator::make(request()->all(), ['email' => 'required', 'password' => 'required']);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'msg' => $validator->errors()->first()], 400);
        }
        $inputs = request()->only('email', 'password');

        $loginUser = $this->userService->loginUser($inputs['email'], $inputs['password']);
        if ($loginUser) {
            $response = new UserResource($loginUser);
            return response()->json(['status' => true, 'data' => $response], 200);
        }
        return response()->json(['status' => false,'msg'=>'Unauthorized'], 401);
    }

    /**
        * @SWG\Post(
        *     path="/api/v2/sendCode",
        *     description="Send code to user mail",
        *     tags = {"user"},
        *     @SWG\Parameter(
        *         name="email",
        *         in="query",
        *         type="string",
        *         description="user email",
        *         required=true,
        *     ),
        *     @SWG\Response(
        *         response=200,
        *         description="code sent to your mail",
        *        examples={
        *     "application/json": { "status": true, "data": {} }
        *      }
        *     ),
        *     @SWG\Response(
        *         response=401,
        *         description="",
        *        examples={
        *     "application/json": { "status": false, "msg": "user not found" }
        *      }
        *     )
        *  )
        */

    public function sendCode()
    {
        $validator = Validator::make(request()->all(), [
        'email' => 'required|email'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'msg' => $validator->errors()->first()], 200);
        }


        if (!empty(request('email'))) {
            $user = User::where('email', request('email'))->first();
            if ($user) {
                $code = mt_rand(1000, 9999);
                $user->update(['reset_code' => $code]);

                $user->sendRememberCode($code);

                $data['code'] = $code;
                return response()->json(['status' => true,'data' => $data, 'msg' => 'Code sent to your mail successfully'], 200);
            }
            return response()->json(['status' => false, 'msg' => 'user not found'], 200);
        }
    }

    /**
        * @SWG\Post(
        *     path="/api/v2/checkCode",
        *     description="check code which sent to user mail",
        *     tags = {"user"},
        *     @SWG\Parameter(
        *         name="email",
        *         in="query",
        *         type="string",
        *         description="user email",
        *         required=true,
        *     ),
        *     @SWG\Parameter(
        *         name="code",
        *         in="query",
        *         type="string",
        *         description="the code which send to mail",
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
        *     "application/json": { "status": false, "msg": "user not found" }
        *      }
        *     )
        *  )
        */

    public function checkCode()
    {
        $validator = Validator::make(request()->all(), [
        'email' => 'nullable|email',
        'code' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'msg' => $validator->errors()->first()], 200);
        }


        $user = User::where('email', request('email'))->first();

        if ($user) {
            if ($user->reset_code == request('code')) {
                return response()->json(['status'=>  true,'msg' => 'code is correct'], 200);
            }
            return response()->json(['status' => false, 'msg' => 'code is not correct'], 200);
        }
        return response()->json(['status' => false, 'msg' => 'user not found'], 200);
    }

    /**
        * @SWG\Post(
        *     path="/api/v2/updatePassword",
        *     description="updatePassword of user with new password",
        *     tags = {"user"},
        *     @SWG\Parameter(
        *         name="email",
        *         in="query",
        *         type="string",
        *         description="user email",
        *         required=true,
        *     ),
        *     @SWG\Parameter(
        *         name="new_password",
        *         in="query",
        *         type="string",
        *         description="new_password of user",
        *         required=true,
        *     ),
        *     @SWG\Parameter(
        *         name="confirmation_password",
        *         in="query",
        *         type="string",
        *         description="confirmation_password of user password",
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
        *     "application/json": { "status": false, "msg": "user not found" }
        *      }
        *     )
        *  )
        */

    public function updatePassword()
    {
        $validator = Validator::make(request()->all(), [
        'email' => 'nullable|email',
        'new_password' => 'required',
        'confirmation_password' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false,'msg' => $validator->errors()->first()], 200);
        }

        if (request('new_password') !== request('confirmation_password')) {
            return response()->json(['status' => false,'msg' => 'password and confirmation password do not matched'], 200);
        }

        $user = User::where('email', request('email'))->first();




        if ($user) {
            $user->update(['password' => bcrypt(request('new_password'))]);
            return response()->json(['status' => true, 'msg' => 'password has reset successfully'], 200);
        }
        return response()->json(['status' => false, 'msg' => 'user not found'], 200);
    }



    public function redirectToProvider($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    public function handleProviderCallback($provider)
    {
        try {
            $user = Socialite::driver($provider)->user();
        } catch (Exception $e) {
            return redirect('/login');
        }

        $authUser = $this->findOrCreateUser($user, $provider);
        Auth::login($authUser, true);
        return redirect($this->redirectTo);
    }


    public function findOrCreateUser($providerUser, $provider)
    {
        $account = SocialIdentity::whereProviderName($provider)
                 ->whereProviderId($providerUser->getId())
                 ->first();

        if ($account) {
            return $account->user;
        } else {
            $user = User::whereEmail($providerUser->getEmail())->first();

            if (! $user) {
                $user = User::create([
                  'email' => $providerUser->getEmail(),
                  'name'  => $providerUser->getName(),
              ]);
            }

            $user->identities()->create([
              'provider_id'   => $providerUser->getId(),
              'provider_name' => $provider,
          ]);

            return $user;
        }
    }
}
