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
        Schema::create('rider_register_requests', function (Blueprint $table) {
            $table->id();
            $table->string('division');
            $table->string('district');
            $table->string('vehicle');
            $table->string('name');
            $table->string('contact');
            $table->string('email');
            $table->string('eighteenPlus');
            $table->string('termsCondition');
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
        Schema::dropIfExists('rider_register_requests');
    }
};
