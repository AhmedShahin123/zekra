<?php

namespace Tests\Feature;

use App\Models\Transaction;
use App\Models\UserAddress;
use App\Models\Order;
use App\Models\User;
use App\Models\Payment;
use Faker\Generator as Faker;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FailedTransaction extends TestCase
{
    private static $appVersion = "v2";

    private static $orderResponseStructure = [
      'id',
      'user_id',
      'shipping_address_id',
      'shipping_address',
      'shipping_phone',
      'fee' => ['local' => ['value', 'code', 'symbol'], 'original' => ['value', 'code', 'symbol']],
      'tax' => ['local' => ['value', 'code', 'symbol'], 'original' => ['value', 'code', 'symbol']],
      'total' => ['local' => ['value', 'code', 'symbol'], 'original' => ['value', 'code', 'symbol']],
      'progress_status',
      'delivery_status',
      'progress_status_date',
      'delivery_status_date',
      'payment_status',
      'album_count',
      'refundable',
      'partner',
      'courier',
      'is_paid',
      'has_albums'
  ];
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testExample()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
    public function testFailedTransaction()
    {
        $user = factory(User::class)->create();
        $address = factory(UserAddress::class)->make();
        $user->addresses()->save($address);
        $this->actingAs($user, 'api');

        $response = $this->post('/api/'.self::$appVersion.'/orders', ['address_id' => $user->defaultAddress->id]);
        $data = $response->getData();

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'status',
            'msg',
            'data' => self::$orderResponseStructure
        ]);
        $response->assertJson(['status' => true, 'msg' => 'Order created successfully']);
        $response->assertJson(['data' => ['user_id' => $user->id]]);
        $response->assertJson(['data' => ['shipping_address_id' => $address->id]]);
        $response->assertJson(['data' => ['is_paid' => false]]);
        $response->assertJson(['data' => ['has_albums' => false]]);

        $order = $data->data;


        //$payment = factory(Payment::class)->create(['user_id' => $user->id, 'purchased_type' => 'App\Models\Order', 'purchased_id' => $order->id,'payment_method' => 'cod','money_amount' => $order->total,'points_amount' => 3,'status' =>0]);

        $this->assertDatabaseHas('payments', [
            'status' => 0
        ]);



        $this->assertDatabaseHas('payments', [
            'purchased_id' => 1,
            'status' => 1
        ]);
    }
}
