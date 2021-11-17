<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Coupon;
use Faker\Generator as Faker;

$factory->define(Coupon::class, function (Faker $faker) {
    return [
        'type'          => array_random(['discount', 'invitation']),
        'code'          => str_random(),
        'value_type'    => array_random(['money', 'points']),
        'value'         => mt_rand(1, 50),
        'usage_times'   => mt_rand(1, 5),
        'expire_at'     => $faker->date(),
        'active'        => mt_rand(0, 1)
    ];
});
