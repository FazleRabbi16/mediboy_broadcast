<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users');
            $table->unsignedBigInteger('pharmacy_id');
            $table->foreign('pharmacy_id')->references('id')->on('pharmacies');
            $table->string('orderNo');
            $table->string('type');
            $table->string('area');
            $table->string('payment_method');
            $table->float('offer_total_amount');
            $table->string('status');
            $table->float('deliveryCharge')->default(0.00);
            $table->float('offer_deliveryCharge')->default(0.00);
            $table->float('offer_grandTotal');
            $table->float('comission_amount');
            $table->integer('delivery_confirmation_code');
            $table->date('orderDate');
            $table->string('coupon')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
};
