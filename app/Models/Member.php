<?php

namespace App\Models;

use Database\Factories\MemberFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Member extends Model
{
    /** @use HasFactory<MemberFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'contact_number',
        'address',
    ];

    /**
     * Walk-in members have no user account, so this can be null
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return HasMany<Borrowing, $this> */
    public function borrowings(): HasMany
    {
        return $this->hasMany(Borrowing::class);
    }

    /**
     * Adds active_borrowings_count: books requested or out right now
     *
     * @param  Builder<Member>  $query
     */
    public function scopeWithActiveLoanCount(Builder $query): void
    {
        $query->withCount([
            'borrowings as active_borrowings_count' => fn (Builder $q) => $q->whereIn('status', ['pending', 'borrowed']),
        ]);
    }
}
