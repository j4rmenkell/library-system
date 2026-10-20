<?php

namespace Database\Factories;

use App\Models\Member;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Member>
 */
class MemberFactory extends Factory
{
    /**
     * A walk-in member by default (no user account).
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => null,
            'name' => fake()->name(),
            'contact_number' => '0917 555 '.fake()->numerify('####'),
            'address' => fake()->city(),
        ];
    }

    // A reader who signed up online: a borrower account plus this profile
    public function withAccount(): static
    {
        return $this->state(fn () => ['user_id' => User::factory()]);
    }
}
