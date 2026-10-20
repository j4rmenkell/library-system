<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Member;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use RuntimeException;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (app()->isProduction()) {
            $this->seedProductionAdmin();
        } else {
            $this->seedTestAccounts();
        }

        // The mythologies; `php artisan books:import` fills them with books
        foreach (['Greek', 'Norse', 'Roman', 'Egyptian', 'Philippine'] as $mythology) {
            Category::firstOrCreate(['name' => "{$mythology} Mythology"]);
        }
    }

    // The live site gets only one account, with credentials from the
    // environment (secrets in Azure), never a guessable password
    private function seedProductionAdmin(): void
    {
        $admin = config('library.admin');

        if (empty($admin['email']) || strlen((string) $admin['password']) < 12) {
            throw new RuntimeException('Set ADMIN_EMAIL and an ADMIN_PASSWORD of at least 12 characters before seeding production.');
        }

        // Created directly, not through factories: factories need Faker, a dev
        // package that the production Docker image leaves out
        // Seeding twice keeps the existing admin
        if (User::where('email', $admin['email'])->exists()) {
            return;
        }

        User::forceCreate([
            'email' => $admin['email'],
            'name' => $admin['name'],
            'username' => 'admin',
            'role' => 'admin',
            'password' => $admin['password'],
            'email_verified_at' => now(),
        ]);
    }

    // One account per role for local development; password is "password"
    private function seedTestAccounts(): void
    {
        foreach ([
            ['Johann Batiancila', 'admin'],
            ['Ana Reyes', 'librarian'],
            ['Juan Dela Cruz', 'borrower'],
        ] as [$name, $role]) {
            User::forceCreate([
                'name' => $name,
                'username' => $role,
                'email' => "{$role}@library.test",
                'role' => $role,
                'password' => 'password',
                'email_verified_at' => now(),
            ]);
        }

        // Readers who sign up get a member profile, so the seeded one does too
        $borrower = User::where('username', 'borrower')->firstOrFail();

        Member::create([
            'user_id' => $borrower->id,
            'name' => $borrower->name,
            'contact_number' => '0917 555 0123',
            'address' => 'Brgy. San Antonio, Pasig City',
        ]);
    }
}
