<?php

namespace Database\Seeders;

use App\Models\Pharmacy;
use App\Models\PharmacyUser;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PharmacyUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $pharmacy = Pharmacy::first();
        $limit=3;
        for ($i=0; $i <$limit ; $i++) {
            PharmacyUser::create([
                'firstName'=>'John',
                'lastName'=>'Doe',
                'phoneNumber'=>random_int(10000000000,99999999999),
                'email'=>Str::random(10).'@gmail.com',
                'password'=>bcrypt(123456),
                'role'=>'Admin',
                'pharmacy_id'=>$pharmacy->id
            ]);
        }
    }
}
