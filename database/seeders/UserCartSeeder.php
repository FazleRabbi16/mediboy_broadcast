<?php

namespace Database\Seeders;

use App\Models\Cart;
use App\Models\User;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserCartSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $userIds = User::take(5)->pluck('id');
        $productIds = Product::take(5)->pluck('id');
        foreach ($userIds as $userId) {
            foreach ($productIds as $productId) {
                Cart::create([
                    'user_id' => $userId,
                    'product_id' => $productId,
                    'quantity' => rand(1, 5),
                ]);
             }
          }
    }
}
