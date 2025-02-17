<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('activities', function (Blueprint $table) {
            $table->id('activity_id');
            $table->string('type');
            $table->text('description');
            $table->unsignedBigInteger('performed_by')->nullable();
            $table->unsignedBigInteger('boarding_house_id')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->foreign('performed_by')
                  ->references('user_id')
                  ->on('users')
                  ->onDelete('set null');
                  
            $table->foreign('boarding_house_id')
                  ->references('boarding_house_id')
                  ->on('boarding_houses')
                  ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('activities');
    }
};