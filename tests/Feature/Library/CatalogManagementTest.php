<?php

namespace Tests\Feature\Library;

use App\Models\Book;
use App\Models\Borrowing;
use App\Models\Category;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// Categories, books and members: the pages librarians use to run the library
class CatalogManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $librarian;

    protected function setUp(): void
    {
        parent::setUp();

        $this->librarian = User::factory()->librarian()->create();
    }

    public function test_categories_can_be_added_but_not_duplicated(): void
    {
        $this->actingAs($this->librarian)->post('/categories', ['name' => 'Norse Mythology'])->assertRedirect('/categories');
        $this->actingAs($this->librarian)->post('/categories', ['name' => 'Norse Mythology'])->assertSessionHasErrors('name');

        $this->assertSame(1, Category::where('name', 'Norse Mythology')->count());
        $this->assertDatabaseHas('logs', ['action' => 'Added category Norse Mythology']);
    }

    public function test_a_category_with_books_cannot_be_deleted(): void
    {
        $used = Book::factory()->create()->category;
        $empty = Category::factory()->create();

        $this->actingAs($this->librarian)->delete("/categories/{$used->id}");
        $this->actingAs($this->librarian)->delete("/categories/{$empty->id}");

        $this->assertModelExists($used);
        $this->assertModelMissing($empty);
    }

    public function test_adding_a_book_cleans_the_isbn_and_puts_it_on_the_shelf(): void
    {
        $category = Category::factory()->create();

        $this->actingAs($this->librarian)->post('/books', [
            'title' => 'Norse Mythology',
            'author' => 'Neil Gaiman',
            'isbn' => '978-0-393-35618-2',
            'publication_year' => 2017,
            'category_id' => $category->id,
            'availability_status' => 'borrowed',
        ])->assertRedirect('/books');

        $this->assertDatabaseHas('books', ['isbn' => '9780393356182', 'availability_status' => 'available']);
    }

    public function test_isbns_must_be_valid_and_unique(): void
    {
        $existing = Book::factory()->create();
        $base = ['title' => 'T', 'author' => 'A', 'category_id' => $existing->category_id];

        $this->actingAs($this->librarian)->post('/books', [...$base, 'isbn' => $existing->isbn])->assertSessionHasErrors('isbn');
        $this->actingAs($this->librarian)->post('/books', [...$base, 'isbn' => '12345'])->assertSessionHasErrors('isbn');
        $this->actingAs($this->librarian)->post('/books', [...$base, 'isbn' => '0-14-044755-X'])->assertSessionHasNoErrors();
    }

    public function test_book_search_matches_title_author_and_isbn(): void
    {
        Book::factory()->create(['title' => 'Circe', 'author' => 'Madeline Miller', 'isbn' => '9780316556347']);
        Book::factory()->create(['title' => 'Mythos', 'author' => 'Stephen Fry', 'isbn' => '9781405934138']);

        $this->actingAs($this->librarian)->get('/books?search=miller')
            ->assertInertia(fn ($page) => $page->where('books.total', 1)->where('books.data.0.title', 'Circe'));
        $this->actingAs($this->librarian)->get('/books?search=978-1405')
            ->assertInertia(fn ($page) => $page->where('books.total', 1)->where('books.data.0.title', 'Mythos'));
    }

    public function test_a_book_with_history_cannot_be_deleted(): void
    {
        $withHistory = Borrowing::factory()->returned()->create()->book;
        $unused = Book::factory()->create();

        $this->actingAs($this->librarian)->delete("/books/{$withHistory->id}");
        $this->actingAs($this->librarian)->delete("/books/{$unused->id}");

        $this->assertModelExists($withHistory);
        $this->assertModelMissing($unused);
    }

    public function test_walk_in_members_can_be_registered(): void
    {
        $this->actingAs($this->librarian)
            ->post('/members', ['name' => 'Rosa Aquino', 'contact_number' => '0919 555 0134', 'address' => 'Valenzuela'])
            ->assertRedirect();

        $this->assertDatabaseHas('members', ['name' => 'Rosa Aquino', 'user_id' => null]);
        $this->actingAs($this->librarian)
            ->post('/members', ['name' => 'X', 'contact_number' => 'abc', 'address' => 'Y'])
            ->assertSessionHasErrors('contact_number');
    }

    public function test_the_members_list_counts_only_active_loans(): void
    {
        $member = Member::factory()->create();
        Borrowing::factory()->for($member)->create();
        Borrowing::factory()->borrowed()->for($member)->create();
        Borrowing::factory()->returned()->for($member)->create();

        $this->actingAs($this->librarian)
            ->get('/members')
            ->assertInertia(fn ($page) => $page->where('members.data.0.active_borrowings_count', 2));
    }

    public function test_readers_cannot_open_the_staff_pages(): void
    {
        $reader = User::factory()->create();

        foreach (['/categories', '/books', '/members', '/borrowings'] as $page) {
            $this->actingAs($reader)->get($page)->assertForbidden();
        }
    }
}
