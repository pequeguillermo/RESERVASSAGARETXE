<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ResetSuperadminPinSeeder extends Seeder
{
    /**
     * Resetea el access_code del superadministrador a 9999.
     * Uso: php artisan db:seed --class=ResetSuperadminPinSeeder
     */
    public function run(): void
    {
        $updated = DB::table('users')
            ->where('role', 'superadmin')
            ->update(['access_code' => '9999']);

        if ($updated > 0) {
            $this->command->info("✅ PIN del superadministrador actualizado a 9999 correctamente.");
            $this->command->info("   Filas actualizadas: {$updated}");
        } else {
            $this->command->warn("⚠️  No se encontró ningún usuario con rol 'superadmin'.");
        }
    }
}
