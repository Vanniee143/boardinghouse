<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('support_requests', function (Blueprint $table) {
            $table->id('support_id');
            $table->string('subject');
            $table->text('description');
            $table->enum('priority', ['low', 'medium', 'high']);
            $table->foreignId('user_id')->constrained('users', 'user_id');
            $table->string('user_name');
            $table->enum('status', ['pending', 'in_progress', 'resolved'])->default('pending');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('support_requests');
    }
}; 