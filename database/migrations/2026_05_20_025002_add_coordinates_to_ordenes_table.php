<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('ordenes', function (Blueprint $table) {
            $table->decimal('lat_destino', 10, 8)->nullable()->after('estado_entrega');
            $table->decimal('lng_destino', 11, 8)->nullable()->after('lat_destino');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ordenes', function (Blueprint $table) {
            //
        });
    }
};
