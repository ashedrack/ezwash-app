<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Models\Company;
use App\Models\Employee;
use Faker\Generator as Faker;

$factory->define(Company::class, function (Faker $faker) {

    return [
        'name' => $faker->company
    ];
});
