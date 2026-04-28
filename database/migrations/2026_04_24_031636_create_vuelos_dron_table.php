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
        Schema::create('vuelos_dron', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dron_id')->constrained('drones')->onDelete('cascade');
            $table->foreignId('orden_id')->constrained('ordenes')->onDelete('cascade');
            $table->dateTime('hora_despegue')->nullable();
            $table->dateTime('hora_aterrizaje')->nullable();
            $table->decimal('lat_origen', 10, 7);
            $table->decimal('lng_origen', 10, 7);
            $table->decimal('lat_destino', 10, 7);
            $table->decimal('lng_destino', 10, 7);
            $table->decimal('carga_kg', 5, 2);
            $table->enum('estado_mision', ['programado','en_vuelo','completado','fallido','cancelado'])->default('programado');
            $table->text('notas')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vuelos_dron');
    }
};
