<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FilmSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $genres = \App\Models\genre::all();

        \App\Models\Film::factory(10)->create()->each(function ($film) use ($genres) {
            // Attach 1-3 random genres
            $film->genres()->attach(
                $genres->random(rand(1, 3))->pluck('id')->toArray()
            );

            // Create a polymorphic image
            $film->image()->create([
                'path' => 'https://picsum.photos/seed/' . $film->id . '/800/1200',
            ]);
        });
    }
}
