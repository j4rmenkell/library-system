<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\Borrowing;
use App\Models\Member;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Borrowing>
 */
class BorrowingFactory extends Factory
{
    /**
     * A request waiting for the librarian by default.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'book_id' => Book::factory(),
            'member_id' => Member::factory(),
            'status' => 'pending',
        ];
    }

    // Out on loan, due in a week. The book is marked borrowed too.
    public function borrowed(int $dueInDays = 7): static
    {
        return $this->state([
            'status' => 'borrowed',
            'borrow_date' => today()->subDays(14 - $dueInDays),
            'due_date' => today()->addDays($dueInDays),
        ])->for(Book::factory()->borrowed());
    }

    public function overdue(int $daysLate = 3): static
    {
        return $this->borrowed(-$daysLate);
    }

    public function returned(): static
    {
        return $this->state([
            'status' => 'returned',
            'borrow_date' => today()->subDays(20),
            'due_date' => today()->subDays(6),
            'return_date' => today()->subDays(8),
        ]);
    }
}
