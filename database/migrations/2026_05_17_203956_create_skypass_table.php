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
        Schema::create('skypass', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('plan', ['mensual', 'trimestral', 'anual']);
            $table->decimal('precio_pagado', 10, 2);
            $table->dateTime('inicio');
            $table->dateTime('vencimiento');
            $table->boolean('activo')->default(true);
            $table->string('mp_payment_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('skypass');
    }
};
