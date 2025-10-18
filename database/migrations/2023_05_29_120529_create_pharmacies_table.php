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
        Schema::create('pharmacies', function (Blueprint $table) {
            $table->id();
            $table->string('pharmacyName')->unique();
            $table->string('drug_lic_no');
            $table->string('proprietor');
            $table->string('contact');
            $table->string('division');
            $table->string('district');
            $table->string('upazilla');
            $table->string('area');
            $table->string('placeDetails');
            $table->string('googleLink');
            $table->string('openTime');
            $table->string('closeTime');
            $table->string('offDays')->nullable();
            $table->string('cover_image');
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
        Schema::dropIfExists('pharmacies');
    }
};
