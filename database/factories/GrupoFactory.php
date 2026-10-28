<?php

namespace Database\Factories;

use App\Models\Curso;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Grupo>
 */
class GrupoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'curso_id' => Curso::factory(),
            'numero_ciclo' => fake()->numberBetween(1, 3),
            'anio' => fake()->numberBetween(2020, 2026),
            'numero_grupo' => fake()->numberBetween(1, 10),
            'abierto' => true,
        ];
    }
}
