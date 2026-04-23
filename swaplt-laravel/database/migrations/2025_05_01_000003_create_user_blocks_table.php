<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('user_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blocker_id')->constrained('users')->onDelete('cascade'); // Usuario que bloquea
            $table->foreignId('blocked_id')->constrained('users')->onDelete('cascade'); // Usuario bloqueado
            $table->text('razon')->nullable(); // Razón opcional del bloqueo
            $table->timestamps();

            // Índice compuesto para evitar bloqueos duplicados
            $table->unique(['blocker_id', 'blocked_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_blocks');
    }
}; 