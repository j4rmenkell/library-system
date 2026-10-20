<?php

namespace Tests\Feature\Library;

use App\Models\Book;
use App\Models\Borrowing;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReaderBorrowingTest extends TestCase
{
    use RefreshDatabase;

    private User $reader;

    private Member $member;

    protected function setUp(): void
    {
        parent::setUp();

        $this->member = Member::factory()->withAccount()->create();
        $this->reader = $this->member->user;
    }

    public function test_a_reader_can_request_an_available_book(): void
    {
        $book = Book::factory()->create();

        $this->actingAs($this->reader)->post("/catalog/{$book->id}/borrow")->assertRedirect();

        $this->assertDatabaseHas('borrowings', ['book_id' => $book->id, 'member_id' => $this->member->id, 'status' => 'pending']);
        // The book stays on the shelf until the librarian hands it over
        $this->assertSame('available', $book->fresh()->availability_status);
    }

    public function test_a_book_someone_else_asked_for_cannot_be_requested(): void
    {
        $taken = Borrowing::factory()->create();

        $this->actingAs($this->reader)->post("/catalog/{$taken->book_id}/borrow");

        $this->assertSame(1, Borrowing::where('book_id', $taken->book_id)->count());
    }

    public function test_a_reader_cannot_request_more_than_the_limit(): void
    {
        Borrowing::factory()->count(config('library.max_active_loans'))->for($this->member)->create();
        $book = Book::factory()->create();

        $this->actingAs($this->reader)->post("/catalog/{$book->id}/borrow");

        $this->assertDatabaseMissing('borrowings', ['book_id' => $book->id]);
    }

    public function test_the_catalog_shows_loans_and_requests(): void
    {
        $requested = Borrowing::factory()->for($this->member)->create();

        $this->actingAs($this->reader)
            ->get('/catalog')
            ->assertInertia(fn ($page) => $page
                ->component('catalog/index')
                ->where('activeLoans', 1)
                ->where('requestedBookIds', [$requested->book_id]));
    }

    public function test_a_reader_can_cancel_their_own_request_only(): void
    {
        $mine = Borrowing::factory()->for($this->member)->create();
        $theirs = Borrowing::factory()->create();

        $this->actingAs($this->reader)->delete("/my-borrowings/{$mine->id}")->assertRedirect();
        $this->actingAs($this->reader)->delete("/my-borrowings/{$theirs->id}")->assertForbidden();

        $this->assertDatabaseMissing('borrowings', ['id' => $mine->id]);
        $this->assertDatabaseHas('borrowings', ['id' => $theirs->id]);
    }

    public function test_a_book_already_collected_cannot_be_cancelled(): void
    {
        $loan = Borrowing::factory()->borrowed()->for($this->member)->create();

        $this->actingAs($this->reader)->delete("/my-borrowings/{$loan->id}");

        $this->assertSame('borrowed', $loan->fresh()->status);
    }

    public function test_my_borrowings_lists_only_my_books(): void
    {
        Borrowing::factory()->for($this->member)->create();
        Borrowing::factory()->create();

        $this->actingAs($this->reader)
            ->get('/my-borrowings')
            ->assertInertia(fn ($page) => $page->has('borrowings', 1));
    }

    public function test_staff_cannot_use_the_reader_pages(): void
    {
        $this->actingAs(User::factory()->librarian()->create())->get('/catalog')->assertForbidden();
    }
}
