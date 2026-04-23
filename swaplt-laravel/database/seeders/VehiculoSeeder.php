<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Vehiculo;

class VehiculoSeeder extends Seeder
{
    public function run(): void
    {
        Vehiculo::factory(50)->create(); // Genera 50 vehículos de prueba
    }
}
