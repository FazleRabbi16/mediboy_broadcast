<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
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
            User::create([
                'firstName'=>'Akbar',
                'lastName'=>'Hossain',
                'phoneNumber'=>random_int(10000000000,99999999999),
                'password'=>bcrypt(123456),
                'division'=>'Dhaka',
                'district'=>'Narayanganj',
                'subDistrict'=>'Fatullah',
                'bloodGroup'=>'AB+',
                'donateBlood'=>'Yes',
                'termsConditions'=>'signed',
                'status'=>'new',
            ]);
        }
        for ($i=0; $i<$limit ; $i++) {
            User::create([
                'firstName'=>'Akbar',
                'lastName'=>'Hossain',
                'phoneNumber'=>random_int(10000000000,99999999999),
                'password'=>bcrypt(123456),
                'division'=>'Dhaka',
                'district'=>'Narayanganj',
                'subDistrict'=>'Fatullah',
                'bloodGroup'=>'AB+',
                'donateBlood'=>'Yes',
                'termsConditions'=>'signed',
                'status'=>'active',
            ]);
        }
        for ($i=0; $i<$limit ; $i++) {
            User::create([
                'firstName'=>'Akbar',
                'lastName'=>'Hossain',
                'phoneNumber'=>random_int(10000000000,99999999999),
                'password'=>bcrypt(123456),
                'division'=>'Dhaka',
                'district'=>'Narayanganj',
                'subDistrict'=>'Fatullah',
                'bloodGroup'=>'AB+',
                'donateBlood'=>'Yes',
                'termsConditions'=>'signed',
                'status'=>'trash',
            ]);
        }
    }
}
