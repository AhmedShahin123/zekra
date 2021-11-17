<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\UserCard;
use Faker\Generator as Faker;

$factory->define(UserCard::class, function (Faker $faker) {
    return [
        'card_token'    => 'tok_visa',
        'brand'         => $faker->creditCardType,
        'exp_month'     => mt_rand(1, 12),
        'exp_year'      => mt_rand(2022, 2030),
        'last4'         => mt_rand(1111, 9999),
        'default'       => 1
    ];
});
