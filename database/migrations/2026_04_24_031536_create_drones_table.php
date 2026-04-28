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
        Schema::create('drones', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('modelo');
            $table->string('numero_serie')->unique();
            $table->string('fabricante');
            $table->date('fecha_adquisicion');
            $table->unsignedInteger('autonomia_min');      // minutos de batería
            $table->decimal('velocidad_max_kmh', 5, 1);
            $table->decimal('alcance_max_km', 5, 1);
            $table->decimal('carga_max_kg', 5, 2);         // restricción de peso
            $table->unsignedInteger('bateria_minima_pct')->default(20); // % mínimo para volar
            $table->unsignedInteger('bateria_actual_pct')->default(100);
            $table->json('zonas_permitidas')->nullable();  // array de polígonos/radios
            $table->json('condiciones_climaticas')->nullable(); // viento_max, lluvia, etc.
            $table->enum('estado', ['disponible', 'en_vuelo', 'mantenimiento', 'fuera_servicio'])->default('disponible');
            // coordenadas actuales (simuladas)
            $table->decimal('lat_actual', 10, 7)->nullable();
            $table->decimal('lng_actual', 10, 7)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drones');
    }
};
