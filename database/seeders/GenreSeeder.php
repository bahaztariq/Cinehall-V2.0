<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GenreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $genres = [
            ['name' => 'Action'],
            ['name' => 'Comedy'],
            ['name' => 'Drama'],
            ['name' => 'Horror'],
            ['name' => 'Sci-Fi'],
            ['name' => 'Romance'],
            ['name' => 'Thriller'],
            ['name' => 'Animation'],
            ['name' => 'Documentary'],
            ['name' => 'Adventure'],
        ];

        foreach ($genres as $genre) {
            \App\Models\genre::updateOrCreate($genre);
        }
    }
}
