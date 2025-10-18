<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
       $category = Category::first();
       $company= Company::first();
       $limit=50;
        for ($i=0; $i<$limit ; $i++) {
        Product::create([
            'productName' =>Str::random(15),
            'genericName' => Str::random(20),
            'retail_max_price' => 100.2,
            'cart_qty_inc' => 10,
            'cart_text' => 'cart text',
            'unit_in_pack' =>"10 unit in a strip",
            'type' => 'capsul',
            'quantity' => "250 ml",
            'prescription' => 'yes',
            'feature' => 'yes',
            'description' => Str::random(150),
            'coverImage' => 'test.jpg',
            'category_id' => $category->id,
            'company_id' => $company->id,
        ]);
    }
  }
}
