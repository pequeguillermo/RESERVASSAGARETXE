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
        \App\Models\User::updateOrCreate(
            ['email' => 'admin@sagaretxe.com'],
            [
                'name' => 'Administrador',
                'password' => \Illuminate\Support\Facades\Hash::make('Sagaretxe2026'),
                'email_verified_at' => now(),
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \App\Models\User::where('email', 'admin@sagaretxe.com')->delete();
    }
};
