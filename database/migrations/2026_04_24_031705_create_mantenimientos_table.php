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
        Schema::create('mantenimientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dron_id')->constrained('drones')->onDelete('cascade');
            $table->enum('tipo', ['preventivo', 'correctivo']);
            $table->string('descripcion');
            $table->date('fecha_programada');
            $table->date('fecha_realizada')->nullable();
            $table->decimal('costo', 10, 2)->nullable();
            $table->string('tecnico')->nullable();
            $table->enum('estado', ['pendiente', 'en_proceso', 'completado', 'cancelado'])->default('pendiente');
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mantenimientos');
    }
};
