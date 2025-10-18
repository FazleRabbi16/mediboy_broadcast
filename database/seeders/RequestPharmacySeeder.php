<?php

namespace Database\Seeders;

use App\Models\RequestPharmacy;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class RequestPharmacySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $limit = 10;
        for ($i=0; $i <$limit; $i++) {
            RequestPharmacy::create([
                'fullName'=>'John Doe',
                'contact'=>random_int(10000000000,99999999999),
                'email'=>'test_pharmacy@gmail.com',
                'pharmacyName'=>'Larze Pharma',
                'address'=>'fatullah,narayanganj,dhaka',
                'place'=>'1230 hiway , Dhaka narayanganj link road oposite of Somobai market',
                'status'=>'request',
            ]);
        }
    }
}
