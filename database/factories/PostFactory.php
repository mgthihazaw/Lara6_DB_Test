<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Model;
use App\Post;
use Faker\Generator as Faker;

$factory->define(Post::class, function (Faker $faker) {
    return [
        'title' => $faker->title,
        'description' => $faker->sentence,
        'user_id'  => 1
    ];
});