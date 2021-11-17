<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserTest extends TestCase
{
    public function testCreateUser()
    {
        $user = factory(User::class)->create();
        $this->assertDatabaseHas('users', $user->toArray());
    }

    public function testLoginUser()
    {
        $user = factory(User::class)->create();
//        $this->userService->loginUser($user->email, $user->password);

        $response = $this->post('/api/v2/login', ['email' => $user->email, 'password' => $user->password]);
        $response->assertStatus(401);
        $jsonResponse = json_decode($response->getContent());
        $response->assertJson(['status' => false, 'msg' => 'Unauthorized']);
    }

    public function testSendCodeUser()
    {
        $user = factory(User::class)->create();

        $response = $this->post('/api/v2/sendCode', ['email' => $user->email]);
        $jsonResponse = json_decode($response->getContent());

        $response->assertStatus(200);
        $response->assertJson(['status' => true, 'msg' => 'Code sent to your mail successfully']);
    }
    //
    public function testCheckCodeUser()
    {
        $user = factory(User::class)->create();

        $response = $this->post('/api/v2/checkCode', ['email' => $user->email,'code' => $user->reset_code]);
        $jsonResponse = json_decode($response->getContent());

        $response->assertStatus(200);
        $response->assertJson(['status' => false,'msg' => 'The code field is required.']);
    }
    //
    public function testUpdatePassword()
    {
        $user = factory(User::class)->create();

        $response = $this->post('/api/v2/updatePassword', ['email' => $user->email,'new_password' => 12345678, 'confirmation_password' => 12345678]);
        $jsonResponse = json_decode($response->getContent());

        $response->assertStatus(200);
        $response->assertJson(['status' => true,'msg' => 'password has reset successfully']);
    }
    //
    public function testUpdateWithInvalidPassword()
    {
        $user = factory(User::class)->create();

        $response = $this->post('/api/v2/updatePassword', ['email' => $user->email,'new_password' => 12345678, 'confirmation_password' => 123456]);
        $jsonResponse = json_decode($response->getContent());

        $response->assertStatus(200);
        $response->assertJson(['status' => false,'msg' => 'password and confirmation password do not matched']);
    }
}
