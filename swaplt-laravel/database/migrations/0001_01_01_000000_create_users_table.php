<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id(); // Clave primaria auto-incremental
            $table->string('name'); // Nombre del usuario
            $table->string('email')->unique(); // Correo electrónico único
            $table->string('password'); // Contraseña encriptada
            $table->enum('rol', ['admin', 'user'])->default('user'); // Rol por defecto: usuario normal
            $table->timestamp('email_verified_at')->nullable(); // Fecha de verificación del correo
            $table->string('verification_code')->nullable(); // Código único de verificación
            $table->rememberToken(); // Token para recordar sesión
            $table->string('reset_token')->nullable(); // Campo para almacenar el token de restablecimiento
            $table->string('google_id')->nullable(); // ID de Google
            $table->string('avatar')->nullable(); // URL del avatar
            $table->timestamps(); // created_at y updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
