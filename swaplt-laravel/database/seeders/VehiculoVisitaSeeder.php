<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Vehiculo;
use App\Models\User;
use Carbon\Carbon;

class VehiculoVisitaSeeder extends Seeder
{
    public function run()
    {
        // Obtener todos los vehículos
        $vehiculos = Vehiculo::all();
        
        // Obtener todos los usuarios
        $usuarios = User::all();
        
        // Para cada vehículo, generar visitas aleatorias en los últimos 30 días
        foreach ($vehiculos as $vehiculo) {
            // Generar entre 50 y 200 visitas por vehículo
            $numVisitas = rand(50, 200);
            
            for ($i = 0; $i < $numVisitas; $i++) {
                // Generar una fecha aleatoria en los últimos 30 días
                $fecha = Carbon::now()->subDays(rand(0, 30))->setHour(rand(0, 23))->setMinute(rand(0, 59));
                
                // 70% de probabilidad de que la visita sea de un usuario registrado
                $userId = rand(0, 100) < 70 ? $usuarios->random()->id : null;
                
                // Crear la visita
                $vehiculo->visitas()->create([
                    'user_id' => $userId,
                    'ip_address' => '192.168.1.' . rand(1, 255),
                    'fecha_visita' => $fecha
                ]);
            }
        }
    }
} 