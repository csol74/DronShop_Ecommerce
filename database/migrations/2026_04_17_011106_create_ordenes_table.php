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
        Schema::create('ordenes', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique(); // ORD-20240001
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('estado', ['pendiente', 'pagado', 'en_despacho', 'entregado', 'cancelado'])->default('pendiente');
            $table->enum('transporte', ['dron', 'moto', 'carro'])->default('moto');
            $table->decimal('subtotal', 12, 2);
            $table->decimal('costo_envio', 10, 2)->default(0);
            $table->decimal('iva', 10, 2)->default(0);
            $table->decimal('total', 12, 2);
            $table->string('direccion_entrega');
            $table->string('ciudad')->default('Bucaramanga');
            $table->text('notas')->nullable();
            // MercadoPago
            $table->string('mp_preference_id')->nullable();
            $table->string('mp_payment_id')->nullable();
            $table->string('mp_status')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ordenes');
    }
};
