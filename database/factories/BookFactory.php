<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Book>
 */
class BookFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category_id' => Category::factory(),
            'title' => rtrim(fake()->sentence(3), '.'),
            'author' => fake()->name(),
            'isbn' => fake()->unique()->isbn13(),
            'publication_year' => fake()->numberBetween(1900, 2025),
            'availability_status' => 'available',
            'cover_path' => null,
        ];
    }

    public function borrowed(): static
    {
        return $this->state(['availability_status' => 'borrowed']);
    }
}
