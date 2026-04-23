<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('vehiculos_reportes', function (Blueprint $table) {
            $table->id(); // ID autoincremental
            $table->foreignId('vehiculo_id')->constrained('vehiculos')->onDelete('cascade'); // Relación con vehiculos
            $table->string('pdf_url'); // URL del archivo PDF generado
            $table->timestamps(); // created_at y updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehiculos_reportes');
    }
};
