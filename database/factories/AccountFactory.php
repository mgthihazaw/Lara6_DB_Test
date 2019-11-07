<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Account;
use App\Model;
use Faker\Generator as Faker;


$factory->define(Account::class, function (Faker $faker) {
    return [
        'username' => $faker->name,
        'address'  => $faker->address,
        'city'     => $faker->city,
        'state'    => $faker->state,
        'phone'    => $faker->phoneNumber,
        'user_id'  => 1
    ];
});