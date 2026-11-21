<?php

namespace Tests\Feature\Library;

use App\Models\Book;
use App\Models\BookCover;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

// Uploading a cover from the book form, and showing it afterwards
class BookCoverTest extends TestCase
{
    use RefreshDatabase;

    private User $librarian;

    protected function setUp(): void
    {
        parent::setUp();

        $this->librarian = User::factory()->librarian()->create();
    }

    // tiny real pictures, so the test does not need GD to draw them
    private function picture(string $name): UploadedFile
    {
        $bytes = [
            'jpg' => '/9j/4AAQSkZJRgABAQEASABIAAD/2wBDAP//////////////////////////////////////////////////////////////////////////////////////wgALCAABAAEBAREA/8QAFBABAAAAAAAAAAAAAAAAAAAAAP/aAAgBAQABPxA=',
            'png' => 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==',
            'webp' => 'UklGRhoAAABXRUJQVlA4TA0AAAAvAAAAEAcQERGIiP4HAA==',
        ][pathinfo($name, PATHINFO_EXTENSION)];

        return UploadedFile::fake()->createWithContent($name, base64_decode($bytes));
    }

    private function newBook(array $overrides = []): array
    {
        return [
            'title' => 'The Tides of Atlantis',
            'author' => 'Ilaya Montserrat',
            'isbn' => '978-971-0-12345-2',
            'publication_year' => 2024,
            'category_id' => Category::factory()->create()->id,
            ...$overrides,
        ];
    }

    public function test_a_book_can_be_added_with_a_cover(): void
    {
        $this->actingAs($this->librarian)
            ->post('/books', $this->newBook(['cover' => $this->picture('atlantis.jpg')]))
            ->assertSessionHasNoErrors();

        $book = Book::firstWhere('title', 'The Tides of Atlantis');

        $this->assertStringStartsWith('upload-', $book->cover_path);
        $this->assertSame('image/jpeg', $book->cover->mime_type);
        $this->assertStringContainsString("/books/{$book->id}/cover?v=", $book->cover_url);
    }

    public function test_a_book_without_a_cover_still_saves(): void
    {
        $this->actingAs($this->librarian)->post('/books', $this->newBook())->assertSessionHasNoErrors();

        $this->assertNull(Book::first()->cover_url);
        $this->assertSame(0, BookCover::count());
    }

    public function test_anyone_can_see_an_uploaded_cover(): void
    {
        $this->actingAs($this->librarian)
            ->post('/books', $this->newBook(['cover' => $this->picture('atlantis.png')]));
        $book = Book::first();

        auth()->logout();

        $this->get("/books/{$book->id}/cover")
            ->assertOk()
            ->assertHeader('Content-Type', 'image/png')
            ->assertHeader('X-Content-Type-Options', 'nosniff');
    }

    public function test_only_small_images_are_accepted(): void
    {
        $this->actingAs($this->librarian)
            ->post('/books', $this->newBook(['cover' => UploadedFile::fake()->create('notes.pdf', 100, 'application/pdf')]))
            ->assertSessionHasErrors('cover');

        $this->actingAs($this->librarian)
            ->post('/books', $this->newBook(['cover' => UploadedFile::fake()->create('evil.svg', 5, 'image/svg+xml')]))
            ->assertSessionHasErrors('cover');

        $this->actingAs($this->librarian)
            ->post('/books', $this->newBook(['cover' => $this->picture('huge.jpg')->size(3000)]))
            ->assertSessionHasErrors('cover');

        $this->assertSame(0, Book::count());
    }

    public function test_editing_replaces_the_cover_and_changes_its_url(): void
    {
        $book = Book::factory()->create(['cover_path' => 'https://covers.openlibrary.org/b/id/1-L.jpg']);

        $this->actingAs($this->librarian)
            ->put("/books/{$book->id}", $this->newBook(['isbn' => $book->isbn, 'cover' => $this->picture('first.jpg')]))
            ->assertSessionHasNoErrors();
        $firstUrl = $book->fresh()->cover_url;

        $this->actingAs($this->librarian)
            ->put("/books/{$book->id}", $this->newBook(['isbn' => $book->isbn, 'cover' => $this->picture('second.webp')]));

        $this->assertNotSame($firstUrl, $book->fresh()->cover_url);
        $this->assertSame(1, BookCover::count());
        $this->assertSame('image/webp', $book->cover()->first()->mime_type);
    }

    public function test_editing_without_a_new_file_keeps_the_cover(): void
    {
        $book = Book::factory()->create(['cover_path' => 'https://covers.openlibrary.org/b/id/1-L.jpg']);

        $this->actingAs($this->librarian)->put("/books/{$book->id}", $this->newBook(['isbn' => $book->isbn]));

        $this->assertSame('https://covers.openlibrary.org/b/id/1-L.jpg', $book->fresh()->cover_url);
    }

    public function test_deleting_a_book_deletes_its_cover(): void
    {
        $this->actingAs($this->librarian)
            ->post('/books', $this->newBook(['cover' => $this->picture('atlantis.jpg')]));

        $this->actingAs($this->librarian)->delete('/books/'.Book::first()->id);

        $this->assertSame(0, BookCover::count());
    }

    public function test_a_missing_cover_is_a_404(): void
    {
        $book = Book::factory()->create();

        $this->get("/books/{$book->id}/cover")->assertNotFound();
        $this->get('/books/abc/cover')->assertNotFound();
    }
}
