<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('mensajes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('emisor_id')->constrained('users')->onDelete('cascade'); // Usuario que envía el mensaje
            $table->foreignId('receptor_id')->constrained('users')->onDelete('cascade'); // Usuario que recibe el mensaje
            $table->text('contenido'); // Contenido del mensaje
            $table->boolean('leido')->default(false); // Estado de leído o no leído
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mensajes');
    }
};
