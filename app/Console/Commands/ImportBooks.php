<?php

namespace App\Console\Commands;

use App\Models\Book;
use App\Models\Category;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ImportBooks extends Command
{
    protected $signature = 'books:import {--per=12 : How many books to import per category}';

    protected $description = 'Import mythology books and their covers from Open Library';

    public function handle(): int
    {
        $categories = Category::orderBy('name')->get();

        if ($categories->isEmpty()) {
            $this->error('Add categories first. Each category name is used as the Open Library subject.');

            return self::FAILURE;
        }

        foreach ($categories as $category) {
            $this->info("{$category->name}:");
            $added = 0;
            $authors = [];

            foreach ($this->search($category->name) as $doc) {
                if ($added >= (int) $this->option('per')) {
                    break;
                }

                $isbn = $this->pickIsbn($doc['isbn'] ?? []);
                $author = $doc['author_name'][0];

                // Skip books we can't use, authors we already have two of, and
                // books already on the shelf (same ISBN, or another edition of
                // the same title by the same author)
                if (! $isbn
                    || $this->isForeignScript($doc['title'])
                    || ($authors[$author] ?? 0) >= 2
                    || Book::where('isbn', $isbn)->exists()
                    || Book::where('title', $doc['title'])->where('author', $author)->exists()) {
                    continue;
                }

                $coverPath = $this->downloadCover($doc['cover_i'], $isbn);

                Book::create([
                    'category_id' => $category->id,
                    'title' => mb_strimwidth($doc['title'], 0, 255),
                    'author' => $author,
                    'isbn' => $isbn,
                    'publication_year' => $doc['first_publish_year'] ?? null,
                    'availability_status' => 'available',
                    'cover_path' => $coverPath,
                ]);

                $added++;
                $authors[$author] = ($authors[$author] ?? 0) + 1;
                $this->line("  + {$doc['title']}".($coverPath ? '' : ' (no cover)'));
            }

            $this->line("  {$added} added");
        }

        return self::SUCCESS;
    }

    /**
     * Books for one subject that have a cover, an author and an ISBN.
     *
     * @return array<int, array<string, mixed>>
     */
    private function search(string $subject): array
    {
        $response = Http::timeout(30)->retry(2, 1000)->get('https://openlibrary.org/search.json', [
            'q' => 'subject:"'.strtolower($subject).'" language:eng',
            'fields' => 'title,author_name,isbn,first_publish_year,cover_i',
            'sort' => 'readinglog',
            'limit' => 60,
        ]);

        return collect($response->json('docs', []))
            ->filter(fn ($doc) => ! empty($doc['cover_i']) && ! empty($doc['author_name']) && ! empty($doc['isbn']))
            ->values()
            ->all();
    }

    /**
     * Prefer a modern 13-digit ISBN, fall back to a 10-digit one.
     *
     * @param  array<int, string>  $isbns
     */
    private function pickIsbn(array $isbns): ?string
    {
        $clean = collect($isbns)->map(fn ($isbn) => strtoupper(preg_replace('/[^0-9Xx]/', '', $isbn)));

        return $clean->first(fn ($isbn) => preg_match('/^97[89]\d{10}$/', $isbn))
            ?? $clean->first(fn ($isbn) => preg_match('/^\d{9}[\dX]$/', $isbn));
    }

    // Titles like "Ἰλιάς" are the original-language edition; we want English ones
    private function isForeignScript(string $title): bool
    {
        return (bool) preg_match('/[\p{Greek}\p{Cyrillic}\p{Arabic}\p{Han}]/u', $title);
    }

    // Saves the large cover to storage/app/public/covers/<isbn>.jpg
    private function downloadCover(int $coverId, string $isbn): ?string
    {
        try {
            $image = Http::timeout(30)->get("https://covers.openlibrary.org/b/id/{$coverId}-L.jpg");

            if (! $image->successful()) {
                return null;
            }

            $path = "covers/{$isbn}.jpg";
            Storage::disk('public')->put($path, $image->body());

            return $path;
        } catch (Throwable) {
            // A missing cover shouldn't stop the whole import
            return null;
        }
    }
}
