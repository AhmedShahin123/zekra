<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Package;
use Faker\Generator as Faker;

$factory->define(Package::class, function (Faker $faker) {
    return [
        'price'         => mt_rand(10, 150),
        'credit_points' => mt_rand(1, 15),
        'max_users'     => mt_rand(1, 50),
        'expire_at'     => $faker->date()
    ];
});
