<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('vehiculos_imagenes', function (Blueprint $table) {
            $table->id(); // ID autoincremental
            $table->foreignId('vehiculo_id')->constrained('vehiculos')->onDelete('cascade'); // Relación con vehículos
            $table->string('imagen_url')->nullable(); // URL de la imagen (opcional)
            $table->string('imagen_path')->nullable(); // Ruta del archivo almacenado
            $table->integer('imagen_order')->default(1); // Orden de las imágenes en la galería
            $table->timestamps(); // created_at y updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehiculos_imagenes');
    }
};
