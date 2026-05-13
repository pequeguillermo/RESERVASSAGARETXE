<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->boolean('allergies')->default(false);
            $table->boolean('celiac')->default(false);
            $table->boolean('strollers')->default(false);
            $table->boolean('reduced_mobility')->default(false);
            $table->boolean('wheelchairs')->default(false);
            $table->integer('adults')->default(0);
            $table->integer('children')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn([
                'allergies', 'celiac', 'strollers', 'reduced_mobility', 'wheelchairs', 'adults', 'children'
            ]);
        });
    }
};
