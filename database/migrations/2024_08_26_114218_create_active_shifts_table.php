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
        Schema::create('active_shifts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rider_shift_id');
            $table->foreign('rider_shift_id')->references('id')->on('rider_shifts');
            $table->unsignedBigInteger('rider_id');
            $table->foreign('rider_id')->references('id')->on('riders');
            $table->string('division');
            $table->string('district');
            $table->string('upazilla');
            $table->string('vehicleType');
            $table->integer('bodyTemp')->nullable();
            $table->boolean('is_online');
            $table->boolean('on_delivery');
            $table->boolean('is_time_over')->nullable();
            $table->time('givenStartTime');
            $table->dateTime('activeDateTime');
            $table->time('givenEndTime');
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
        Schema::dropIfExists('active_shifts');
    }
};
