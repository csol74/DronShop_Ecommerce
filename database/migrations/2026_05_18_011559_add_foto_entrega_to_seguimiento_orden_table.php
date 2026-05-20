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
        Schema::table('seguimiento_orden', function (Blueprint $table) {
            $table->string('foto_entrega')->nullable()->after('lng'); // ruta de la foto
            $table->foreignId('logistica_user_id')->nullable()->constrained('users')->onDelete('set null')->after('foto_entrega');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('seguimiento_orden', function (Blueprint $table) {
            //
        });
    }
};
