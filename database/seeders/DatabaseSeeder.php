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

        User::factory()->admin()->create([
            'name' => 'Maria Santos',
            'username' => 'admin',
            'email' => 'admin@library.test',
        ]);

        User::factory()->librarian()->create([
            'name' => 'Ana Reyes',
            'username' => 'librarian',
            'email' => 'librarian@library.test',
        ]);

        User::factory()->create([
            'name' => 'Juan Dela Cruz',
            'username' => 'borrower',
            'email' => 'borrower@library.test',
        ]);
    }
}
