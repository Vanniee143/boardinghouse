<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('notifications', function (Blueprint $table) {
            if (!Schema::hasColumn('notifications', 'has_attachment')) {
                $table->boolean('has_attachment')->default(false);
            }
            if (!Schema::hasColumn('notifications', 'attachment_type')) {
                $table->string('attachment_type')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropColumn(['has_attachment', 'attachment_type']);
        });
    }
};