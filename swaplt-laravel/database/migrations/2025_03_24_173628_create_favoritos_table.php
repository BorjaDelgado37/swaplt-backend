<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('favoritos', function (Blueprint $table) {
            $table->id(); // ID autoincremental
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Relación con el usuario
            $table->foreignId('vehiculo_id')->constrained('vehiculos')->onDelete('cascade'); // Relación con el vehículo
            $table->timestamps(); // created_at y updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('favoritos');
    }
};
