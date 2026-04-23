<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Mensaje;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Mensaje>
 */
class MensajeFactory extends Factory
{
    public function definition(): array
    {
        // Seleccionar dos usuarios diferentes aleatoriamente
        $emisor = User::inRandomOrder()->first();
        $receptor = User::inRandomOrder()->where('id', '!=', $emisor->id)->first();

        return [
            'emisor_id' => $emisor->id ?? User::factory(),
            'receptor_id' => $receptor->id ?? User::factory(),
            'contenido' => $this->faker->sentence(10), // Mensaje de prueba
            'leido' => $this->faker->boolean(30), // 30% de probabilidad de estar leído
        ];
    }
}
