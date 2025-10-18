<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Models\Pharmacy;
use App\Models\OrderItem;
use App\Models\Prescription;
use App\Models\DeliveryAddress;
use Illuminate\Database\Seeder;
use App\Models\OrderPrescription;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class OrderSeeder extends Seeder
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
        $pharmacyId = Pharmacy::first()->id;
        $deliveryAddressId = DeliveryAddress::first()->id;
        $prescriptionId = Prescription::first()->id;
        foreach ($userIds as $userId) {
            $order = Order::create([
                'user_id' => $userId,
                'pharmacy_id'=>$pharmacyId,
                'type'=>"Home Delivery",
                'delivery_address_id'=>$deliveryAddressId,
                'payment_method'=>'online',
                'total_amount'=>1200,
                'status'=>'confirm',
                'deliveryCharge'=>60,
                'grandTotal'=>1260
            ]);
            $order_id = $order->id;
            OrderPrescription::create([
                'order_id'=>$order_id,
                'prescription_id'=>$prescriptionId
            ]);
            foreach ($productIds as $productId) {
                OrderItem::create([
                    'order_id'=>$order_id,
                    'maxPrice'=>200,
                    'discountPrice'=>180,
                    'product_id' => $productId,
                    'quantity' => rand(1, 5)
                ]);
             }
          }
    }
}
