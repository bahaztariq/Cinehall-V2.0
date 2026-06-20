<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // Seeded admin account for local dev: test@example.com / password
        User::updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name'     => 'Admin User',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'is_admin' => true,
                'status'   => 'active',
            ]
        );

        $this->call([
            GenreSeeder::class,
            RealMoviesSeeder::class,
        ]);
    }
}
