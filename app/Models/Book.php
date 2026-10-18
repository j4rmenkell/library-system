<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Book extends Model
{
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

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function borrowings(): HasMany
    {
        return $this->hasMany(Borrowing::class);
    }

    // Turns "covers/123.jpg" into a full URL the browser can load
    protected function coverUrl(): Attribute
    {
        return Attribute::get(fn () => $this->cover_path
            ? asset('storage/'.$this->cover_path)
            : null);
    }
}
