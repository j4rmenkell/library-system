<?php

namespace Tests\Feature\Library;

use App\Models\Book;
use App\Models\Borrowing;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BorrowingWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $librarian;

    protected function setUp(): void
    {
        parent::setUp();

        $this->librarian = User::factory()->librarian()->create();
    }

    public function test_approving_starts_the_loan_and_marks_the_book_borrowed(): void
    {
        $borrowing = Borrowing::factory()->create();

        $this->actingAs($this->librarian)
            ->patch("/borrowings/{$borrowing->id}/approve")
            ->assertRedirect();

        $borrowing->refresh();
        $this->assertSame('borrowed', $borrowing->status);
        $this->assertTrue($borrowing->borrow_date->isToday());
        $this->assertTrue($borrowing->due_date->equalTo(today()->addDays(config('library.loan_days'))));
        $this->assertSame('borrowed', $borrowing->book->availability_status);
        $this->assertDatabaseHas('logs', ['user_id' => $this->librarian->id]);
    }

    public function test_only_waiting_requests_can_be_approved(): void
    {
        $borrowing = Borrowing::factory()->returned()->create();

        $this->actingAs($this->librarian)->patch("/borrowings/{$borrowing->id}/approve");

        $this->assertSame('returned', $borrowing->fresh()->status);
    }

    public function test_rejecting_leaves_the_book_on_the_shelf(): void
    {
        $borrowing = Borrowing::factory()->create();

        $this->actingAs($this->librarian)->patch("/borrowings/{$borrowing->id}/reject");

        $this->assertSame('rejected', $borrowing->fresh()->status);
        $this->assertSame('available', $borrowing->book->fresh()->availability_status);
    }

    public function test_marking_returned_puts_the_book_back_on_the_shelf(): void
    {
        $borrowing = Borrowing::factory()->borrowed()->create();

        $this->actingAs($this->librarian)->patch("/borrowings/{$borrowing->id}/return");

        $borrowing->refresh();
        $this->assertSame('returned', $borrowing->status);
        $this->assertTrue($borrowing->return_date->isToday());
        $this->assertSame('available', $borrowing->book->availability_status);
    }

    public function test_a_walk_in_issue_starts_the_loan_straight_away(): void
    {
        $member = Member::factory()->create();
        $book = Book::factory()->create();

        $this->actingAs($this->librarian)
            ->post('/borrowings', ['member_id' => $member->id, 'book_id' => $book->id])
            ->assertRedirect('/borrowings?status=borrowed');

        $this->assertDatabaseHas('borrowings', ['member_id' => $member->id, 'book_id' => $book->id, 'status' => 'borrowed']);
        $this->assertSame('borrowed', $book->fresh()->availability_status);
    }

    public function test_a_book_already_out_cannot_be_issued(): void
    {
        $book = Book::factory()->borrowed()->create();

        $this->actingAs($this->librarian)
            ->post('/borrowings', ['member_id' => Member::factory()->create()->id, 'book_id' => $book->id])
            ->assertSessionHasErrors('book_id');
    }

    public function test_a_reader_cannot_go_over_the_loan_limit(): void
    {
        $member = Member::factory()->create();
        Borrowing::factory()->count(config('library.max_active_loans'))->for($member)->create();

        $this->actingAs($this->librarian)
            ->post('/borrowings', ['member_id' => $member->id, 'book_id' => Book::factory()->create()->id])
            ->assertSessionHasErrors('member_id');
    }

    public function test_the_overdue_tab_lists_only_late_books(): void
    {
        Borrowing::factory()->borrowed()->create();
        $late = Borrowing::factory()->overdue()->create();

        $this->actingAs($this->librarian)
            ->get('/borrowings?status=overdue')
            ->assertInertia(fn ($page) => $page
                ->component('borrowings/index')
                ->where('counts.borrowed', 2)
                ->where('counts.overdue', 1)
                ->where('borrowings.data.0.id', $late->id));
    }

    public function test_readers_cannot_manage_borrowings(): void
    {
        $borrowing = Borrowing::factory()->create();
        $reader = User::factory()->create();

        $this->actingAs($reader)->get('/borrowings')->assertForbidden();
        $this->actingAs($reader)->patch("/borrowings/{$borrowing->id}/approve")->assertForbidden();
    }
}
