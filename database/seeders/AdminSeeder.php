<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
            Admin::create([
                'firstName'=>'Akbar',
                'lastName'=>'Hossain',
                'email'=>'admin@gmail.com',
                'password'=>bcrypt(123456),
                'role'=>'Admin',
                'division'=>'Dhaka',
                'district'=>'Narayanganj',
                'upazilla'=>'Fatullah',
                'bloodGroup'=>'AB+',
                'donateBlood'=>'yes',
                'termsConditions'=>'yes',
            ]);

    }
}
