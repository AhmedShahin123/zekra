<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\City;
use App\Models\Country;
use App\Models\User;
use Illuminate\Support\Str;
use Faker\Generator as Faker;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$countriesIds = Country::pluck('id')->toArray();
$citiesIds = City::pluck('id')->toArray();

$factory->define(User::class, function (Faker $faker) use($countriesIds, $citiesIds) {
    return [
        'name'              => $faker->name,
        'email'             => $faker->unique()->safeEmail,
        'email_verified_at' => now(),
        'phone'             => $faker->phoneNumber,
        'country_id'        => array_random($countriesIds),
        'city_id'           => array_random($citiesIds),
        'address'           => $faker->address,
        'status'            => 1,
        'password'          => bcrypt(123456789),
        'remember_token'    => Str::random(10),
    ];
});
