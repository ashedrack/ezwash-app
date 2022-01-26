<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */
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

$factory->define(\App\Models\User::class, function (Faker $faker) {
    $gender = ['male', 'female'][rand(0,1)];
    return [
        'name' => $faker->name($gender),
        'email' => $faker->unique()->safeEmail,
        'phone' => generateTestPhoneNumber($faker->unique()->phoneNumber),
        'gender' => $gender,
        'avatar' => null,
        'password' =>  null,
        'is_active' => 1,
        'email_verified_at' => now(),
        'remember_token' => Str::random(10),
    ];
});
