<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\Order;
use App\Models\Package;
use App\Models\User;
use App\Models\UserAddress;
use App\Models\UserCard;
use App\Models\UserPackage;
use App\Services\Order\OrderPrice;
use Carbon\Carbon;
use Tests\TestCase;
use App\Traits\Helper;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;

class OrderTest extends TestCase
{

    use Helper;

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

    // public function testCreateOrderWithoutAuth()
    // {
    //     $response = $this->withHeaders(['Accept' => 'application/json'])->post('api/'.self::$appVersion.'/orders');
    //     $response->assertStatus(401);
    //     $response->assertJson(['status' => false, 'msg' => 'Unauthenticated.']);
    // }

    public function testCreateOrderWithoutShippingAddress()
    {
        $user = factory(User::class)->create();
        $this->actingAs($user, 'api');

        $response = $this->post('/api/'.self::$appVersion.'/orders');
        $response->assertStatus(400);
        $response->assertJsonStructure(['status', 'msg']);
        $response->assertJson(['status' => false, 'msg' => 'The address id field is required.']);
    }

    public function testCreateOrderWithWrongShippingAddress()
    {
        $user = factory(User::class)->create();
        $this->actingAs($user, 'api');

        $wrongShippingAddress = UserAddress::where('user_id', '!=', $user->id)->first();
        $wrongShippingAddressId = empty($wrongShippingAddress) ? 1 : $wrongShippingAddress->id;
        $response = $this->post('/api/'.self::$appVersion.'/orders', ['address_id' => $wrongShippingAddressId]);
        $response->assertStatus(400);
        $response->assertJsonStructure(['status', 'msg']);
        $response->assertJson(['status' => false, 'msg' => 'The selected address id is invalid.']);
    }

    public function testCreateOrderObject()
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
        return $order;
    }

    /**
     * @depends testCreateOrderObject
     */
    public function testPayForOrderWithoutCreditCard($order)
    {
        $user = User::find($order->user_id);
        $this->actingAs($user, 'api');
        $response = $this->put('/api/'.self::$appVersion.'/orders/'.$order->id.'/pay', ['albums_count' => 3, 'payment_method' => 'credit_card']);
        
        $response->assertStatus(400);
        $response->assertJsonStructure([
            'status', 
            'msg', 
        ]);
        $response->assertJson(['status' => false, 'msg' => 'User does not have a default card']);

    }

    /**
     * @depends testCreateOrderObject
     */
    public function testPayForOrderWithInvalidCreditCard($order)
    {
        $user = User::find($order->user_id);
        $card = factory(UserCard::class)->make(['default' => 1, 'card_token' => 'test-token']); // test-token is invalid token
        $user->cards()->save($card);
        $this->actingAs($user, 'api');
        $response = $this->put('/api/'.self::$appVersion.'/orders/'.$order->id.'/pay', ['albums_count' => 3, 'payment_method' => 'credit_card']);
        
        $response->assertStatus(400);
        $response->assertJsonStructure([
            'status', 
            'msg',
            'data' => ['message'] 
        ]);
        $response->assertJson(['status' => false, 'msg' => 'Payment failed']);

    }

    /**
     * @depends testCreateOrderObject
     */
    public function testPayForOrderWithValidCreditCard($order)
    {
        $user = User::find($order->user_id);
        $card = factory(UserCard::class)->make(['default' => 1, 'card_token' => 'tok_visa']); // tok_visa is valid test token
        $user->cards()->save($card);
        $this->actingAs($user, 'api');
        $response = $this->put('/api/'.self::$appVersion.'/orders/'.$order->id.'/pay', ['albums_count' => 3, 'payment_method' => 'credit_card', 'card_id' => $card->id]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status', 
            'msg',
            'data' => self::$orderResponseStructure 
        ]);
        $response->assertJson(['status' => true, 'msg' => 'Order paid successfully']);
        $response->assertJson(['data' => ['is_paid' => true]]);
        $response->assertJson(['data' => ['has_albums' => false]]);
    }

    public function testPayForOrderWithNotEnoughCreditPoints()
    {
        $order = $this->testCreateOrderObject();
        $user = User::find($order->user_id);
        $this->actingAs($user, 'api');
        $response = $this->put('/api/'.self::$appVersion.'/orders/'.$order->id.'/pay', ['albums_count' => 3, 'payment_method' => 'credit_points']);
        $response->assertStatus(400);
        $response->assertJsonStructure([
            'status', 
            'msg',
        ]);
        $response->assertJson(['status' => false, 'msg' => 'User credit points is not enough']);
    }

    public function testPayForOrderWithEnoughCreditPoints()
    {
        $order = $this->testCreateOrderObject();
        
        // add package to user
        $date = Carbon::today()->addDay();
        $package = factory(Package::class)->create(['credit_points' => 3, 'max_users' => 1, 'expire_at' => $date]);

        // add the package to the user
        $userPackageData = [
            'user_id'               => $order->user_id,
            'package_id'            => $package->id,
            'package_credit_points' => $package->credit_points
        ];
        UserPackage::create($userPackageData);

        $user = User::find($order->user_id);
        $this->actingAs($user, 'api');
        
        $response = $this->put('/api/'.self::$appVersion.'/orders/'.$order->id.'/pay', ['albums_count' => 3, 'payment_method' => 'credit_points']);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status', 
            'msg',
            'data' => self::$orderResponseStructure 
        ]);
        $response->assertJson(['status' => true, 'msg' => 'Order paid successfully']);
        $response->assertJson(['data' => ['is_paid' => true]]);
        $response->assertJson(['data' => ['has_albums' => false]]);
    }
    
    public function testPayForOrderCashOnDelivery()
    {
        $order = $this->testCreateOrderObject();
        $user = User::find($order->user_id);
        $this->actingAs($user, 'api');
        
        $response = $this->put('/api/'.self::$appVersion.'/orders/'.$order->id.'/pay', ['albums_count' => 3, 'payment_method' => 'cod']);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status', 
            'msg',
            'data' => self::$orderResponseStructure 
        ]);
        $response->assertJson(['status' => true, 'msg' => 'Order paid successfully']);
        $response->assertJson(['data' => ['payment_status' => 'Paid']]);
        $response->assertJson(['data' => ['is_paid' => true]]);
        $response->assertJson(['data' => ['has_albums' => false]]);
        
        $order = Order::find($order->id);
        return $order;
    }
    
    public function testPayForOrderAndApplyPointsCoupon()
    {
        $order = $this->testCreateOrderObject();
        
        // create the coupon
        $date = Carbon::now()->addDay();
        $coupon = factory(Coupon::class)->create(['value_type' => 'points', 'value' => 3, 'active' => 1, 'expire_at' => $date]);

        $user = User::find($order->user_id);
        $this->actingAs($user, 'api');
        
        $response = $this->put('/api/'.self::$appVersion.'/orders/'.$order->id.'/pay', ['albums_count' => 3, 'payment_method' => 'credit_points', 'discount_code' => $coupon->code]);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status', 
            'msg',
            'data' => self::$orderResponseStructure 
        ]);
        $response->assertJson(['status' => true, 'msg' => 'Order paid successfully']);
        $response->assertJson(['data' => ['is_paid' => true]]);
        $response->assertJson(['data' => ['has_albums' => false]]);

    }

    public function testPayForOrderAndApplyDiscountCoupon()
    {
        $order = $this->testCreateOrderObject();
        $order = Order::find($order->id);

        // create the coupon
        $date = Carbon::now()->addDay();
        $coupon = factory(Coupon::class)->create(['value_type' => 'money', 'value' => 5, 'active' => 1, 'expire_at' => $date]);

        // get user object
        $user = User::find($order->user_id);
        $this->actingAs($user, 'api');

        // get order price
        $orderPrice = new OrderPrice($user);
        $orderPrice->useOrder($order); // set the order price variable to be the order variables (partner, courier, shipping address, ...)
        $albumsCount = mt_rand(1, 5);
        $priceVariables = $orderPrice->getPriceVariables($albumsCount);
        
        $response = $this->put('/api/'.self::$appVersion.'/orders/'.$order->id.'/pay', ['albums_count' => $albumsCount, 'payment_method' => 'cod', 'discount_code' => $coupon->code]);
        $responseData = $response->getData();
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status', 
            'msg',
            'data' => self::$orderResponseStructure 
        ]);
        $response->assertJson(['status' => true, 'msg' => 'Order paid successfully']);
        $response->assertJson(['data' => ['is_paid' => true]]);
        $response->assertJson(['data' => ['has_albums' => false]]);
        $expectedTotal = ($priceVariables['total'] + $priceVariables['cod']) - $coupon->value;
        $expectedTotal = floatval($expectedTotal);
        $actualTotal = floatval($responseData->data->total->original->value);
        $this->assertSame($expectedTotal, $actualTotal);
    }

    // public function testUploadBase64Images()
    // {
    //     $order = $this->testPayForOrderCashOnDelivery();
    //     $user = User::find($order->user_id);
    //     $this->actingAs($user, 'api');
        
    //     $base64Image = $this->getTestBase64Strings();
    //     $response = $this->post('/api/'.self::$appVersion.'/orders/'.$order->id.'/albums/base64', ['albums' => [
    //         '{"album_name": "test albums", "album_count": 1, "images": ["'.$base64Image[0].'"]}'
    //     ]]);
    //     $response->assertStatus(200);
    //     $response->assertJsonStructure([
    //         'status', 
    //         'msg',
    //         'data' => self::$orderResponseStructure 
    //     ]);
    //     $response->assertJson(['status' => true, 'msg' => 'Albums created successfully']);
    //     $response->assertJson(['data' => ['is_paid' => true]]);
    //     $response->assertJson(['data' => ['has_albums' => true]]);
    // }

    // public function testUploadMultipartImages()
    // {
    //     $order = $this->testPayForOrderCashOnDelivery();
    //     $user = User::find($order->user_id);
    //     $this->actingAs($user, 'api');
        
    //     $image = UploadedFile::fake()->image('avatar.jpg');

    //     $response = $this->post('/api/'.self::$appVersion.'/orders/'.$order->id.'/albums/multipart', [
    //         'albums' => [
    //             ['album_name' => 'new album', 'album_count' => 1, 'images' => [$image]]
    //         ]
    //     ]);
    //     $response->assertStatus(200);
    //     $response->assertJsonStructure([
    //         'status', 
    //         'msg',
    //         'data' => self::$orderResponseStructure 
    //     ]);
    //     $response->assertJson(['status' => true, 'msg' => 'Albums created successfully']);
    //     $response->assertJson(['data' => ['is_paid' => true]]);
    //     $response->assertJson(['data' => ['has_albums' => true]]);
    // }
}
