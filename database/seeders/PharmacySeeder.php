<?php

namespace Database\Seeders;

use App\Models\Pharmacy;
use Illuminate\Support\Str;
use App\Models\PharmacyUser;
use Illuminate\Database\Seeder;
use App\Models\PharmacyBusinessSetup;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PharmacySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $limit=10;
        for ($i=0; $i<$limit ; $i++) {
       $pharmacy = Pharmacy::create([
            'pharmacyName'=>Str::random(20),
            'proprietor'=>Str::random(10),
            'division'=>'Dhaka',
            'district'=>'Narayanganj',
            'subDistrict'=>'Fatullah',
            'area'=>'Chasara',
            'placeDetails'=>'1420 Dhaka Narayanganj Link Road',
            'googleLink'=>'https://shorturl.at/eptGO',
            'openTime'=>'6am,8pm',
            'offDays'=>'Friday',
            'securityCode'=>'12345',
            'cover_image'=>'test.jpg'
        ]);
        $pharmacy_id = $pharmacy->id;
        $status=['active','in-active'];
        $feature=['yes','no'];
        shuffle($status);
        // take random a
        $randomStatus = $status[0];
        shuffle($feature);
        $randomFeatures = $feature[0];
        PharmacyBusinessSetup::create([
            "pharmacy_id"=>$pharmacy_id,
            "status"=>$randomStatus,
            "feature"=>$randomFeatures,
            "comissionPercent"=>20
        ]);

        PharmacyUser::create([
            'firstName'=>'John',
            'lastName'=>'Doe',
            'phoneNumber'=>random_int(10000000000,99999999999),
            'email'=>Str::random(10).'@gmail.com',
            'password'=>bcrypt(123456),
            'role'=>'admin',
            'pharmacy_id'=>$pharmacy_id
        ]);

    }
  }
}
