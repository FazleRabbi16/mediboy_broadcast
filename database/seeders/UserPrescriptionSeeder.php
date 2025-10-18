<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Prescription;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserPrescriptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::first();
        $limit=10;
        for ($i=0; $i <$limit ; $i++) {
            Prescription::create([
                 'image'=>'test.jpg',
                 'user_id'=>$user->id
            ]);
        }
    }
}
