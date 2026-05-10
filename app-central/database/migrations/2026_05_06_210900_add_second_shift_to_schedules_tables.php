<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->time('open_time_2')->nullable()->after('close_time');
            $table->time('close_time_2')->nullable()->after('open_time_2');
        });

        Schema::table('special_schedules', function (Blueprint $table) {
            $table->time('open_time_2')->nullable()->after('close_time');
            $table->time('close_time_2')->nullable()->after('open_time_2');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->dropColumn(['open_time_2', 'close_time_2']);
        });

        Schema::table('special_schedules', function (Blueprint $table) {
            $table->dropColumn(['open_time_2', 'close_time_2']);
        });
    }
};
