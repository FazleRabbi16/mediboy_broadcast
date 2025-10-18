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
        Schema::create('delivery_tos', function (Blueprint $table) {
            $table->id();
            $table->foreign('order_id')->references('id')->on('orders');
            $table->unsignedBigInteger('order_id');
            $table->string('deliveryTo');
            $table->string('deliveryContact');
            $table->string('deliveryAddress');
            $table->string('addressType');
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
        Schema::dropIfExists('delivery_tos');
    }
};
