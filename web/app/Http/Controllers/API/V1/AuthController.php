<?php

namespace App\Http\Controllers\API\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\User\UserServiceInterface;

Use App\Http\Resources\User as UserResource;

use Socialite;
use Validator;

class AuthController extends Controller
{
    public $userService;

    public function __construct(UserServiceInterface $userService)
    {
        $this->userService = $userService;
    }

    public function register()
    {
        $validator = Validator::make(request()->all(), [
            'email'     => 'required|unique:users',
            'password'  => 'required|min:6',
            'phone'     => 'unique:users'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'msg' => $validator->errors()->first()], 400);
        }


        $inputs = request()->only('name', 'phone', 'address', 'email', 'password');
        $registeredUser = $this->userService->registerUser($inputs);

        if(request()->has('platform')){
            // save user platform data
            $data = request()->only('platform', 'device_token', 'ip_address');
            $registeredUser->devices()->create($data);
        }

        $response = new UserResource($registeredUser);
        if ($registeredUser) {
            return response()->json(['status'=>true,'msg' => 'sign up successfully', 'data' => $response], 200);
        }

        
    }


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
