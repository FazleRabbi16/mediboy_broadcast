<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\CompanySeeder;
use Database\Seeders\CategorySeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(CategorySeeder::class);
        $this->call(CompanySeeder::class);
        $this->call(ProductSeeder::class);
        $this->call(AdminSeeder::class);
        $this->call(UserSeeder::class);
        $this->call(PharmacySeeder::class);
        // $this->call(PharmacyUserSeeder::class);
        $this->call(UserAddressSeeder::class);
        $this->call(UserPrescriptionSeeder::class);
        $this->call(UserCartSeeder::class);
        $this->call(OrderSeeder::class);
        $this->call(RequestPharmacySeeder::class);
        $this->call(PharmacyAvailableAreaSeeder::class);

    }
}
