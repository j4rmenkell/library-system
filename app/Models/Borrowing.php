<?php

namespace App\Models;

use Database\Factories\BorrowingFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $status pending, borrowed, returned or rejected
 * @property Carbon|null $borrow_date
 * @property Carbon|null $due_date
 * @property Carbon|null $return_date
 */
class Borrowing extends Model
{
    /** @use HasFactory<BorrowingFactory> */
    use HasFactory;

    protected $fillable = [
        'book_id',
        'member_id',
        'borrow_date',
        'due_date',
        'return_date',
        'status',
    ];

    protected function casts(): array
    {
        // date:Y-m-d keeps them as "2026-10-24" when sent to the front end
        return [
            'borrow_date' => 'date:Y-m-d',
            'due_date' => 'date:Y-m-d',
            'return_date' => 'date:Y-m-d',
        ];
    }

    /** @return BelongsTo<Book, $this> */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    /** @return BelongsTo<Member, $this> */
    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    /**
     * Requested or out on loan: what counts towards a reader's limit
     *
     * @param  Builder<Borrowing>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->whereIn('status', ['pending', 'borrowed']);
    }

    /**
     * Overdue isn't stored: it's a borrowed book whose due date has passed
     *
     * @param  Builder<Borrowing>  $query
     */
    public function scopeOverdue(Builder $query): void
    {
        $query->where('status', 'borrowed')->whereDate('due_date', '<', today());
    }
}
