<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Models\Location;
use Faker\Generator as Faker;

$factory->define(Location::class, function (Faker $faker) {
    return [
        'name' => $faker->streetName,
        'address' => $faker->address,
        'phone' => generateTestPhoneNumber($faker->phoneNumber),
//        'store_image' => $faker->image('public/storage/store_images'),
        'is_active' => 1,
        'number_of_lockers' => 20
    ];
});
