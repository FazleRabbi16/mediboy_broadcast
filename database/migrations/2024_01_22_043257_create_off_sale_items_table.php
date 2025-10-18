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
        Schema::create('off_sale_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('off_sales_id');
            $table->foreign('off_sales_id')->references('id')->on('off_sales');
            $table->unsignedBigInteger('product_id');
            $table->foreign('product_id')->references('id')->on('products');
            $table->float('max_retail_price');
            $table->float('purchase_price');
            $table->float('sale_price');
            $table->float('offer_price');
            $table->float('percentage_off');
            $table->string('batch_no');
            $table->integer('quantity');
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
        Schema::dropIfExists('off_sale_items');
    }
};
