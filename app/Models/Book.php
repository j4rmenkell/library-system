<?php

namespace App\Models;

use Database\Factories\BookFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Book extends Model
{
    /** @use HasFactory<BookFactory> */
    use HasFactory;

    protected $fillable = [
        'category_id',
        'title',
        'author',
        'isbn',
        'publication_year',
        'availability_status',
        'cover_path',
    ];

    // Always include cover_url when a book is sent to the front end
    protected $appends = ['cover_url'];

    /** @return BelongsTo<Category, $this> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /** @return HasMany<Borrowing, $this> */
    public function borrowings(): HasMany
    {
        return $this->hasMany(Borrowing::class);
    }

    /**
     * Imported covers are Open Library URLs and are used as they are;
     * a relative path like "covers/123.jpg" points at local storage
     *
     * @return Attribute<string|null, never>
     */
    protected function coverUrl(): Attribute
    {
        return Attribute::get(fn () => match (true) {
            ! $this->cover_path => null,
            str_starts_with($this->cover_path, 'http') => $this->cover_path,
            default => asset('storage/'.$this->cover_path),
        });
    }
}
