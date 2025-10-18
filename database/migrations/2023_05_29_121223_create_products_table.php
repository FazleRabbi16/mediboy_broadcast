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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('productName');
            $table->string('genericName')->nullable();
            $table->float('retail_max_price',8,2);
            $table->integer('cart_qty_inc');
            $table->string('cart_text');
            $table->string('unit_in_pack')->nullable();
            $table->string('type');
            $table->string('quantity');
            $table->string('prescription');
            $table->string('feature');
            $table->longText('description')->nullable();
            $table->string('status');
            $table->string('coverImage')->nullable();
            $table->unsignedBigInteger('company_id');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->unsignedBigInteger('category_id');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
            $table->string('add_by');
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
        Schema::dropIfExists('products');
    }
};
