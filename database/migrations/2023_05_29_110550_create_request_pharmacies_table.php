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
        Schema::create('request_pharmacies', function (Blueprint $table) {
            $table->id();
            $table->string('fullName');
            $table->string('contact');
            $table->string('email');
            $table->string('pharmacyName');
            $table->string('division');
            $table->string('district');
            $table->string('upazilla');
            $table->string('place');
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
        Schema::dropIfExists('request_pharmacies');
    }
};
