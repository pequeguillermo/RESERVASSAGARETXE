<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('access_code')->nullable()->unique();
        });

        // Set the default user as superadmin and set an access code (e.g. 1234)
        DB::table('users')->where('email', 'admin@sagaretxe.com')->update([
            'role' => 'superadmin',
            'access_code' => '1234'
        ]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('access_code');
        });
    }
};
