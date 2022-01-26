<?php

use Illuminate\Database\Seeder;

class LocationsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $godAdmin = getOverallAdmin('victoria@initsng.com');

        $company = \App\Models\Company::firstOrCreate([
            'name' => 'Ezwash Laundromat',
        ],[
            'owner_id' => $godAdmin->id
        ]);
        $defaultImage = asset('images/dummy_location_image.jpg');
        $locations = [
            [
                "name" => "Osapa",
                "address" => "Monarch Gardens even center, Number 2 Osapa London way, Osapa, Lekki",
                "phone" => "09085689850",
                "store_image" =>  $defaultImage,
                "is_active" => 1,
                "company_id" => $company->id,
                "number_of_lockers" => 20
            ],
            [
                "name" => "Ilupeju",
                "address" => "Ilupeju Mall (Spar) Basement, 31/33 Town planning Way",
                "phone" => "08090559409",
                "store_image" =>  $defaultImage,
                "is_active" => 1,
                "company_id" => $company->id,
                "number_of_lockers" => 20
            ],
            [
                "name" => "Yaba",
                "address" => "E-Centre (Ozone Cinemas) 1st Floor, 1-11 Commercial Avenue, Sabo, Yaba.",
                "phone" => "08095989017",
                "store_image" =>  $defaultImage,
                "is_active" => 1,
                "company_id" => $company->id,
                "number_of_lockers" => 20
            ],
            [
                "name" => "Lekki",
                "address" => "Lush Mall Penthouse, 26A Admiralty Way, Lekki",
                "phone" => "08187853327",
                "store_image" =>  $defaultImage,
                "is_active" => 1,
                "company_id" => $company->id,
                "number_of_lockers" => 20
            ],
            [
                "name" => "IKOTA",
                "address" => "1, Dreamworld Avenue, Ikota",
                "phone" => "08091674296",
                "store_image" =>  $defaultImage,
                "is_active" => 1,
                "company_id" => $company->id,
                "number_of_lockers" => 20
            ]
        ];

        foreach ($locations as $location){
            \App\Models\Location::updateOrCreate([
                "name" => $location['name'],
            ],$location);
        }

    }
}
