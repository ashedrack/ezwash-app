<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Models\Employee;
use Faker\Generator as Faker;

$factory->define(Employee::class, function (Faker $faker) {
    $gender = ['male', 'female'][rand(0,1)];
    return [
        'email' => $faker->email,
		'phone' => generateTestPhoneNumber($faker->unique()->phoneNumber),
		'name' => $faker->name($gender),
		'gender' => $gender,
		'avatar' => null,
		'password' => bcrypt('password')
    ];
});
