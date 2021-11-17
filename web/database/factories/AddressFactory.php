<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\City;
use App\Models\UserAddress;
use Faker\Generator as Faker;

$citiesIds = City::pluck('id')->toArray();

$factory->define(UserAddress::class, function (Faker $faker) use ($citiesIds){
    return [
        'name'          => $faker->words(2, true),
        'city_id'       => array_random($citiesIds),
        'address_1'     => $faker->streetName,
        'address_2'     => $faker->streetAddress,
        'postal_code'   => $faker->postcode,
        'phone'         => $faker->phoneNumber,
        'default'       => 1
    ];
});
