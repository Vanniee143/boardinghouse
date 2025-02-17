<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReviewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id('review_id');
            $table->foreignId('user_id')
                  ->constrained('users', 'user_id')
                  ->onDelete('cascade');
            $table->foreignId('boarding_house_id')
                  ->constrained('boarding_houses', 'boarding_house_id')
                  ->onDelete('cascade');
            $table->foreignId('room_id')
                  ->nullable()
                  ->constrained('rooms', 'room_id')
                  ->onDelete('cascade');
            $table->integer('rating')
                  ->unsigned()
                  ->default(0);
            $table->text('comment')->nullable();
            $table->json('images')->nullable();
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
        Schema::dropIfExists('reviews');
    }
}
