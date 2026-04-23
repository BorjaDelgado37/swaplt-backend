<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Mensaje;

class MensajeSeeder extends Seeder
{
    public function run(): void
    {
        Mensaje::factory(50)->create(); // Genera 50 mensajes aleatorios
    }
}
