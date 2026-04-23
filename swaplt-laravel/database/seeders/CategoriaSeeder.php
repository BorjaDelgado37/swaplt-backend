<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Categoria;

class CategoriaSeeder extends Seeder
{
    public function run(): void
    {
        $categorias = [
            'SUV', 'Sedán', 'Coupé', 'Hatchback', 'Convertible', 'Pickup',
            'Deportivo', 'Furgoneta', 'Eléctrico', 'Híbrido', '4x4'
        ];

        foreach ($categorias as $categoria) {
            Categoria::firstOrCreate(['nombre' => $categoria]);
        }
    }
}
