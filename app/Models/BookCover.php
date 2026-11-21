<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// A cover picture uploaded from the book form (see BookController)
class BookCover extends Model
{
    protected $fillable = [
        'book_id',
        'mime_type',
        'data',
    ];

    /** @return BelongsTo<Book, $this> */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }
}
