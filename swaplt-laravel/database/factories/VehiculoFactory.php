<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Categoria;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Vehiculo>
 */
class VehiculoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::inRandomOrder()->first()->id ?? User::factory(),
            'categoria_id' => Categoria::inRandomOrder()->first()->id ?? Categoria::factory(),
            'marca' => $this->faker->randomElement(['Toyota', 'Ford', 'BMW', 'Mercedes', 'Audi', 'Chevrolet']),
            'modelo' => $this->faker->word(),
            'precio' => $this->faker->randomFloat(2, 5000, 100000),
            'anio' => $this->faker->year(),
            'estado' => $this->faker->randomElement(['nuevo', 'usado']),
            'transmision' => $this->faker->randomElement(['Manual', 'Automática']),
            'tipo_combustible' => $this->faker->randomElement(['Gasolina', 'Diésel', 'Eléctrico', 'Híbrido']),
            'kilometraje' => $this->faker->numberBetween(0, 200000),
            'fuerza' => $this->faker->numberBetween(100, 800),
            'capacidad_motor' => $this->faker->randomFloat(1, 1.0, 6.5),
            'color' => $this->faker->safeColorName(),
            'ubicacion' => $this->faker->city(),
            'matricula' => strtoupper($this->faker->bothify('???-####')),
            'numero_serie' => strtoupper($this->faker->bothify('##??##??##??')),
            'numero_puertas' => $this->faker->randomElement([2, 4, 5]),
            'descripcion' => $this->faker->paragraph(),
            'vehiculo_robado' => $this->faker->randomElement(['Si', 'No']),
            'vehiculo_libre_accidentes' => $this->faker->randomElement(['Si', 'No']),

        ];
    }
}
