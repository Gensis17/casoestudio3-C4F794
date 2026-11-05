<?php

namespace Database\Factories;

use App\Models\Grupo;
use App\Models\Estudiante;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EstudianteMatriculado>
 */
class EstudianteMatriculadoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'grupo_id' => Grupo::factory(),
            'estudiante_id' => Estudiante::factory(),
            'nota' => fake()->randomFloat(2, 0, 100),
        ];
    }
}
