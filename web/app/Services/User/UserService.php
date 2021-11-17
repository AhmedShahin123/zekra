<?php

namespace App\Services\User;

use Hash;
use Auth;
use Socialite;
use App\Repositories\User\UserRepositoryInterface;
use App\Models\User;
use App\Models\City;
use App\Models\Country;

class UserService implements UserServiceInterface
{
    private $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    // user related business functionality

    public function registerUser(array $data)
    {
        $data['originPassword'] = $data['password'];
        $data['password'] = bcrypt($data['password']);

        $user = $this->userRepository->create($data);
        $code = mt_rand(1000, 9999);
        $isUserUpdated = $this->userRepository->update($user, ['reset_code'=>$code]);

        //we need to rewrite notification
        //$user->sendActivationCode($code);
        if (!$isUserUpdated) {
            return null;
        }
        try {
            $user->sendActivationCode();
        } catch (\Exception $e) {
        }
        //$user->sendActivationCode();
        return $this->loginUser($data['email'], $data['originPassword']);
        //return $user;
    }

    public function loginUser(string $email, string $password, string $mobile_token = '')
    {
        if (Auth::attempt(['email' => $email, 'password' => $password])) {
            $user = Auth::user();
            if ($mobile_token) {
                $user = $this->userRepository->update($user, ['token'=>$mobile_token]);
            }

            $success['token'] =  $user->createToken('MyApp')->accessToken;
            $user->token = 'Bearer ' . $success['token'];
            $user->avatar = empty($user->avatar) ? 'users/default_user.png' : $user->avatar;
            $user->name = empty($user->name) ? '' : $user->name;
            $user->phone = empty($user->phone) ? '' : $user->phone;
            $user->reset_code = empty($user->reset_code) ? '' : $user->reset_code;

            return $user;
        }
        return null;
    }

    public function userInfo(array $data)
    {
        $data['city_id'] = $data['city_id'];
        $data['country_id'] = $data['country_id'];

        $city = City::find($data['city_id']);
        $country = Country::find($data['city_id']);

        $data['address'] = $country->country_name . ' , ' .$city->city_name;
        $data['card_token'] = $data['card_token'];
        $user = User::find($data['user_id']);
        if (empty($user)) {
            return null;
        }




        $user = $this->userRepository->find($user->id);

        $isUserUpdated = $this->userRepository->update($user, $data);

        //we need to rewrite notification
        //$user->sendActivationCode($code);
        if (!$isUserUpdated) {
            return null;
        }

        return $user;
    }
}
