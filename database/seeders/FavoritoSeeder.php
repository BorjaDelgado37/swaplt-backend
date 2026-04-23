<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Favorito;

class FavoritoSeeder extends Seeder
{
    public function run(): void
    {
        Favorito::factory(100)->create(); // Genera 100 registros de favoritos de prueba
    }
}
