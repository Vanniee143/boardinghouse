<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('payment_qr_codes', function (Blueprint $table) {
            $table->id('qr_id');
            $table->string('payment_type'); // gcash or paymaya
            $table->string('qr_image');
            $table->string('account_name');
            $table->unsignedBigInteger('boarding_house_id');
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            $table->foreign('boarding_house_id')->references('boarding_house_id')->on('boarding_houses')->onDelete('cascade');
            $table->foreign('created_by')->references('user_id')->on('users');
        });
    }

    public function down()
    {
        Schema::dropIfExists('payment_qr_codes');
    }
}; 