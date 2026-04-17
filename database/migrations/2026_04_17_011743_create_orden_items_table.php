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
    Schema::create('orden_items', function (Blueprint $table) {
        $table->id();
        $table->foreignId('orden_id')->constrained('ordenes')->onDelete('cascade');
        $table->foreignId('producto_id')->constrained()->onDelete('cascade');
        $table->string('nombre_producto'); // snapshot al momento de comprar
        $table->decimal('precio_unitario', 10, 2);
        $table->unsignedInteger('cantidad');
        $table->decimal('subtotal', 12, 2);
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orden_items');
    }
};
