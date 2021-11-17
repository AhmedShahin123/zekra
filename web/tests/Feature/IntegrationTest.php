<?php

namespace Tests\Feature;

use App\Models\User;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class IntegrationTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    private static $appVersion = "v1";
    private static $appVersion2 = "v2";
    public function testExample()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function testApiVersion()
    {
        $response = $this->get('/api/'.self::$appVersion.'/countries');
        $response->assertStatus(200);
        $jsonResponse = json_decode($response->getContent());
        return $jsonResponse;
    }

    public function testApiVersion2()
    {
        $response = $this->get('/api/'.self::$appVersion2.'/countries');
        $response->assertStatus(200);
        $jsonResponse = json_decode($response->getContent());
        return $jsonResponse;
    }

    public function testApiFailedVersion()
    {
        $response = $this->get('/api/v3/countries');
        $response->assertStatus(404);
    }

    public function testWithInvalidMobileToken()
    {
        $response = $this->get('/api/'.self::$appVersion2.'/myOrders', ['HTTP_Authorization' => 'Bearer' . 'asdsafsaassad']);
        $response->assertStatus(500);
        $jsonResponse = json_decode($response->getContent());
        return $jsonResponse;
    }

    public function testWithMobileToken()
    {
        $user = factory(User::class)->create();
        $success['token'] =  $user->createToken('MyApp')->accessToken;
        $user->token = 'Bearer ' . $success['token'];
        $userToken = $user->token;
        $response = $this->get('/api/'.self::$appVersion.'/myOrders', [], ['Authorization' => $userToken]);
        $response->assertStatus(500);
        $jsonResponse = json_decode($response->getContent());
        return $jsonResponse;
    }
}
