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
        Schema::create('m_sale_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('m_sales_id');
            $table->foreign('m_sales_id')->references('id')->on('m_sales');
            $table->unsignedBigInteger('product_id');
            $table->foreign('product_id')->references('id')->on('products');
            $table->float('max_retail_price');
            $table->string('batch_no');
            $table->float('purchase_price');
            $table->float('offer_price');
            $table->integer('quantity');
            $table->float('total_amount');
            $table->float('total_profit');
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
        Schema::dropIfExists('m_sale_items');
    }
};
