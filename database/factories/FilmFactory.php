<?php

namespace Database\Factories;

use App\Models\Film;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Film>
 */
class FilmFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph,
            'duration' => $this->faker->numberBetween(80, 180),
            'rate' => $this->faker->randomElement(['G', 'PG', 'PG-13', 'R', 'NC-17']),
            'trailer' => 'https://www.youtube.com/embed/' . $this->faker->lexify('???????????'),
        ];
    }
}
