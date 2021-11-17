<?php

namespace App\Services\User;

interface UserServiceInterface
{
    public function registerUser(array $data);
    public function loginUser(string $email, string $password, string $mobile_token = '');
}
