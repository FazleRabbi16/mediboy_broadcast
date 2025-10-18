<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CompanySeeder extends Seeder
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
        Company::create([
            'name'=>'ABC Company'
        ]);
      }
    }
}
