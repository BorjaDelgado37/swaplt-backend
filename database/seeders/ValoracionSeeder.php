<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Valoracion;
use App\Models\User;

class ValoracionSeeder extends Seeder
{
    public function run()
    {
        // Obtener algunos usuarios para las valoraciones
        $usuarios = User::inRandomOrder()->take(5)->get();

        // Crear valoraciones entre los usuarios (no se valoran a sí mismos)
        foreach ($usuarios as $emisor) {
            foreach ($usuarios as $receptor) {
                if ($emisor->id !== $receptor->id) {
                    Valoracion::create([
                        'emisor_id' => $emisor->id,
                        'receptor_id' => $receptor->id,
                        'valor' => rand(1, 5),
                        'comentario' => 'Comentario de prueba de ' . $emisor->name . ' a ' . $receptor->name,
                    ]);
                }
            }
        }
    }
} 