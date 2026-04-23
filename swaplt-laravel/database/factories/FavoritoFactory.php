<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Favorito;
use App\Models\User;
use App\Models\Vehiculo;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Favorito>
 */
class FavoritoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::inRandomOrder()->first()->id ?? User::factory(),
            'vehiculo_id' => Vehiculo::inRandomOrder()->first()->id ?? Vehiculo::factory(),
        ];
    }
}
