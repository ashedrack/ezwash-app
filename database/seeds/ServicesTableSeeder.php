<?php

use App\Models\Company;
use Illuminate\Database\Seeder;
use App\Models\Service;

class ServicesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $services = [
            [
                "created_at" => date('Y-m-d h:i:s',1528736992778/1000),
                "name" => "Wash & Dry",
                "price" => 1500
            ],
            [
                "created_at" => date('Y-m-d h:i:s',1528737002868/1000),
                "name" => "2 washes",
                "price" => 2300
            ],
            [
                "created_at" => date('Y-m-d h:i:s',1528737012622/1000),
                "name" => "3 washes",
                "price" => 3200
            ],
            [
                "created_at" => date('Y-m-d h:i:s',1528737021489/1000),
                "name" => "Drop off",
                "price" => 2150
            ],
            [
                "created_at" => date('Y-m-d h:i:s',1528737062171/1000),
                "name" => "2 drop offs",
                "price" => 3500
            ],
            [
                "created_at" => date('Y-m-d h:i:s',1528737197788/1000),
                "name" => "1/2 Dry Cycle",
                "price" => 500
            ],
            [
                "created_at" => date('Y-m-d h:i:s',1528737269514/1000),
                "name" => "Dry Cycle",
                "price" => 1000
            ],
            [
                "created_at" => date('Y-m-d h:i:s',1528737292464/1000),
                "name" => "900 wash",
                "price" => 900
            ],
            [
                "created_at" => date('Y-m-d h:i:s',1528737408898/1000),
                "name" => "800 wash",
                "price" => 800
            ],
            [
                "created_at" => date('Y-m-d h:i:s',1528737423108/1000),
                "name" => "1000 wash",
                "price" => 1000
            ],
            [
                "created_at" => date('Y-m-d h:i:s',1528737433176/1000),
                "name" => "Bleach",
                "price" => 150
            ],
            [
                "created_at" => date('Y-m-d h:i:s',1528737443923/1000),
                "name" => "Ariel",
                "price" => 100
            ],
            [
                "created_at" => date('Y-m-d h:i:s',1528737459226/1000),
                "name" => "Dryer Sheets",
                "price" => 100
            ],
            [
                "created_at" => date('Y-m-d h:i:s',1528737480520/1000),
                "name" => "Tide/Clorox",
                "price" => 300
            ],
            [
                "created_at" => date('Y-m-d h:i:s',1528737494898/1000),
                "name" => "Stain Remover",
                "price" => 300
            ],
            [
                "created_at" => date('Y-m-d h:i:s',1528737505485/1000),
                "name" => "softner",
                "price" => 150
            ],
            [
                "created_at" => date('Y-m-d h:i:s',1528737524038/1000),
                "name" => "My Washbag",
                "price" => 2000
            ],
            [
                "created_at" => date('Y-m-d h:i:s',1528737716168/1000),
                "name" => "ALAT Wash & dry",
                "price" => 1200
            ],
            [
                "created_at" => date('Y-m-d h:i:s',1528737736376/1000),
                "name" => "ALAT 2 Washes",
                "price" => 1840
            ],
            [
                "created_at" => date('Y-m-d h:i:s',1528737775564/1000),
                "name" => "ALAT 3 Washes",
                "price" => 2560
            ],
            [
                "created_at" => date('Y-m-d h:i:s',1528737826349/1000),
                "name" => "ALAT Drop Off",
                "price" => 1680
            ],
            [
                "created_at" => date('Y-m-d h:i:s',1528737923949/1000),
                "name" => "ALAT 2 Drop Offs",
                "price" => 2720
            ],
            [
                "created_at" => date('Y-m-d h:i:s',1529339321006/1000),
                "name" => "Ironing",
                "price" => 120
            ],
            [
                "created_at" => date('Y-m-d h:i:s',1531569989567/1000),
                "name" => "1200 wash",
                "price" => 1200
            ],
            [
                "created_at" => date('Y-m-d h:i:s',1532155123011/1000),
                "name" => "comp wash",
                "price" => -10000
            ],
            [
                "created_at" => date('Y-m-d h:i:s',1540296946350/1000),
                "name" => "Home Pickup",
                "price" => 1200
            ],
            [
                "created_at" => date('Y-m-d h:i:s',1563885811830/1000),
                "name" => "Test",
                "price" => 50
            ]
        ];
        foreach ($services as $service){
            $service['company_id'] = Company::EZWASH_MAIN;
            Service::firstOrCreate([
                'company_id' => Company::EZWASH_MAIN,
                'name' => $service['name']
            ],$service);
        }
    }
}
