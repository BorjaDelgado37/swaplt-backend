<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('vehiculos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('categoria_id')->constrained('categorias')->onDelete('cascade');
            $table->string('marca');
            $table->string('modelo');
            $table->decimal('precio', 10, 2);
            $table->integer('anio');
            $table->enum('estado', ['nuevo', 'usado']);
            $table->string('transmision');
            $table->string('tipo_combustible');
            $table->integer('kilometraje');
            $table->integer('fuerza');
            $table->decimal('capacidad_motor', 3, 1);
            $table->string('color');
            $table->string('ubicacion');
            $table->string('matricula');
            $table->string('numero_serie');
            $table->integer('numero_puertas');
            $table->text('descripcion');
            $table->text('vehiculo_robado');
            $table->text('vehiculo_libre_accidentes');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('vehiculos');
    }
};
