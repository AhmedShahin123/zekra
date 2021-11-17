<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\CouponUser;
use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CouponTest extends TestCase
{

    private static $appVersion = "v2";

    // public function testGetCouponWithoutAuth()
    // {
    //     $coupon = factory(Coupon::class)->create();
    //     $response = $this->withHeaders(['Accept' => 'application/json'])->get('api/'.self::$appVersion.'/coupons/'.$coupon->code);
    //     $response->assertStatus(401);
    //     $response->assertJson(['status' => false, 'msg' => 'Unauthenticated.']);
    // }

    public function testGetPointsCouponValue()
    {        
        // create the coupon
        $date = Carbon::now()->addDay();
        $coupon = factory(Coupon::class)->create(['value_type' => 'points', 'active' => 1, 'expire_at' => $date]);
        
        // create testing user
        $user = factory(User::class)->create();
        $this->actingAs($user, 'api');
        
        // hit the api endpoint 
        $response = $this->get('/api/'.self::$appVersion.'/coupons/'.$coupon->code);
        $data = $response->getData();

        $response->assertStatus(200);
        $response->assertJson(['status' => true, 'data' => ['value_type' => 'points', 'is_discount' => false, 'active' => true]]);
        $response->assertJsonStructure(
            [
                'status',
                'data' => [
                    'id',
                    'is_discount',
                    'code',
                    'value_type',
                    'value',
                    'usage_times',
                    'expire_at',
                    'active'
                ]
            ]
        );
        $this->assertIsNumeric($data->data->value);
    }

    public function testGetDiscountCouponValue()
    {        
        // create the coupon
        $date = Carbon::now()->addDay();
        $coupon = factory(Coupon::class)->create(['value_type' => 'money', 'active' => 1, 'expire_at' => $date]);
        
        // create testing user
        $user = factory(User::class)->create();
        $this->actingAs($user, 'api');
        
        // hit the api endpoint 
        $response = $this->get('/api/'.self::$appVersion.'/coupons/'.$coupon->code);

        $response->assertStatus(200);
        $response->assertJson(['status' => true, 'data' => ['value_type' => 'money', 'is_discount' => true, 'active' => true]]);
        $response->assertJsonStructure(
            [
                'status',
                'data' => [
                    'id',
                    'is_discount',
                    'code',
                    'value_type',
                    'value' => ['original' => ['value', 'code', 'symbol'], 'local' => ['value', 'code', 'symbol']],
                    'usage_times',
                    'expire_at',
                    'active'
                ]
            ]
        );
    }

    public function testGetDisabledCoupon()
    {
        // create the coupon
        $date = Carbon::today()->addDays(1);
        $coupon = factory(Coupon::class)->create(['expire_at' => $date, 'active' => 0]);
        
        // create testing user
        $user = factory(User::class)->create();
        $this->actingAs($user, 'api');
        
        // hit the api endpoint 
        $response = $this->get('/api/'.self::$appVersion.'/coupons/'.$coupon->code);
        $response->assertStatus(400);
        $response->assertJson(['status' => false, 'msg' => 'This code has been disabled']);
    }

    public function testGetExpiredCoupon()
    {
        // create the coupon
        $date = Carbon::today()->addDays(-1);
        $coupon = factory(Coupon::class)->create(['expire_at' => $date, 'active' => 1, 'type' => 'discount']); // type equal discount because only discount coupons has expire date
        
        // create testing user
        $user = factory(User::class)->create();
        $this->actingAs($user, 'api');
        
        // hit the api endpoint 
        $response = $this->get('/api/'.self::$appVersion.'/coupons/'.$coupon->code);
        $response->assertStatus(400);
        $response->assertJson(['status' => false, 'msg' => 'This code has expired']);
    }

    /**
     * @depends Tests\Feature\OrderTest::testCreateOrderObject
     */
    public function testGetUsedBeforeCoupon($order)
    {
        $user = User::find($order->user_id);
        $this->actingAs($user, 'api');

        // create the coupon
        $date = Carbon::today()->addDays(1);
        $coupon = factory(Coupon::class)->create(['expire_at' => $date, 'active' => 1]);
        CouponUser::create(['coupon_id' => $coupon->id,'user_id' => $user->id, 'order_id' => $order->id, 'coupon_code' => $coupon->code, 'value_type' => $coupon->value_type, 'value' => $coupon->value]);
        
        // hit the api endpoint 
        $response = $this->get('/api/'.self::$appVersion.'/coupons/'.$coupon->code);
        $response->assertStatus(400);
        $response->assertJson(['status' => false, 'msg' => 'This user has used this code before']);
    }

    /**
     * @depends Tests\Feature\OrderTest::testCreateOrderObject
     */
    public function testCouponHasExceededUsageLimit($order)
    {
        // create testing user
        $user = factory(User::class)->create();
        $this->actingAs($user, 'api');

        // create the coupon
        $date = Carbon::today()->addDays(1);
        $coupon = factory(Coupon::class)->create(['expire_at' => $date, 'active' => 1, 'usage_times' => 1]);
        CouponUser::create(['coupon_id' => $coupon->id,'user_id' => $order->user_id, 'order_id' => $order->id, 'coupon_code' => $coupon->code, 'value_type' => $coupon->value_type, 'value' => $coupon->value]);
        
        // hit the api endpoint 
        $response = $this->get('/api/'.self::$appVersion.'/coupons/'.$coupon->code);
        $response->assertStatus(400);
        $response->assertJson(['status' => false, 'msg' => 'This code has exceeded its usage limit']);
    }
}
