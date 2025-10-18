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
        Schema::create('stocks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pharmacy_id');
            $table->foreign('pharmacy_id')->references('id')->on('pharmacies');
            $table->unsignedBigInteger('product_id');
            $table->foreign('product_id')->references('id')->on('products');
            $table->unsignedBigInteger('m_sales_id')->nullable();
            $table->foreign('m_sales_id')->references('id')->on('m_sales');
            $table->unsignedBigInteger('off_sales_id')->nullable();
            $table->foreign('off_sales_id')->references('id')->on('off_sales');
            $table->unsignedBigInteger('stock_product_id')->nullable();
            $table->foreign('stock_product_id')->references('id')->on('stock_products');
            $table->string('batch_no');
            $table->integer('stock_in')->nullable();
            $table->integer('stock_out')->nullable();
            $table->date('mfg_date');
            $table->date('expire_date');
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
        Schema::dropIfExists('stocks');
    }
};
