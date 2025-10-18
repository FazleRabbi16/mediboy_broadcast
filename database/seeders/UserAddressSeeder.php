<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Str;
use App\Models\DeliveryAddress;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserAddressSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::first();
        $limit=3;
        for ($i=0; $i <$limit ; $i++) {
            DeliveryAddress::create([
                'fullName'=>'Akbar Hossain',
                'contactNumber'=>'01616815056',
                 'division'=>'Dhaka',
                 'district'=>'Narayanganj',
                 'subDistrict'=>'Fatullah',
                 'pickupPoint'=>'1no gate Fakir gate',
                 'extraInfo'=>'Mojumdar Bari 2nd floor',
                 'type'=>'Home Delivery',
                 'user_id'=>$user->id
            ]);
        }
    }
}
