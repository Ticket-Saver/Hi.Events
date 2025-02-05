<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            // Primero eliminamos las columnas existentes
            $table->dropColumn(['position', 'seat_number']);
        });

        Schema::table('tickets', function (Blueprint $table) {
            // Luego las creamos de nuevo con el tipo correcto
            $table->string('position', 50)->nullable()->after('status');
            $table->string('seat_number', 10)->nullable()->after('position');
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn(['position', 'seat_number']);
        });
    }
}; 