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
        Schema::create('riders', function (Blueprint $table) {
            $table->id();
            $table->string('firstName');
            $table->string('lastName');
            $table->string('contact');
            $table->string('email');
            $table->string('password');
            $table->string('profileImage');
            $table->string('vehicle');
            $table->string('currAdd_division');
            $table->string('currAdd_district');
            $table->string('currAdd_upazilla');
            $table->string('perAdd_division');
            $table->string('perAdd_district');
            $table->string('perAdd_upazilla');
            $table->string('eighteenPlus');
            $table->integer('ref_id')->nullable();
            $table->string('status');
            $table->string('agreementSigned');
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
        Schema::dropIfExists('riders');
    }
};
