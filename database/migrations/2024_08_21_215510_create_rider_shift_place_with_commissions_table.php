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
        Schema::create('rider_shift_place_with_commissions', function (Blueprint $table) {
            $table->id();
            $table->string('division');
            $table->string('district');
            $table->string('upazilla');
            $table->integer('commission');
            $table->string('status');
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
        Schema::dropIfExists('rider_shift_place_with_commissions');
    }
};
