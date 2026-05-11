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
        Schema::table('members', function (Blueprint $table) {
            $table->string('surname')->nullable()->after('name');
            $table->string('dni')->nullable()->after('surname');
            $table->string('postal_code')->nullable()->after('dni');
            $table->date('birth_date')->nullable()->after('postal_code');
            $table->string('address')->nullable()->after('birth_date');
            
            // Preferencias
            $table->string('pref_space')->nullable(); // barra, sala
            $table->string('pref_food')->nullable(); // carne, pescado
            $table->string('pref_drink1')->nullable(); // cerveza, sidra
            $table->string('pref_drink2')->nullable(); // vino tinto, vino blanco
            $table->string('pref_time')->nullable(); // entre semana, fin de semana
            
            // Cómo nos conoció
            $table->string('how_knew_us')->nullable(); // prensa, tv, internet, por un conocido, vecino del barrio
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn([
                'surname',
                'dni',
                'postal_code',
                'birth_date',
                'address',
                'pref_space',
                'pref_food',
                'pref_drink1',
                'pref_drink2',
                'pref_time',
                'how_knew_us'
            ]);
        });
    }
};
