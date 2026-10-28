<?php

namespace App\Models\Oversight;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A supervisor's spot check at a school. Written only by VerificationService.
 */
class VerificationVisit extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'visited_at' => 'datetime',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'organization_id');
    }

    public function visitor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'visited_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(VerificationVisitItem::class);
    }

    // Share of titles whose shelf count matched the system
    public function stockAccuracy(): ?float
    {
        return $this->stock_lines ? $this->stock_lines_matching / $this->stock_lines : null;
    }

    // Share of books recorded with students that the students could show
    public function holdingRate(): ?float
    {
        return $this->books_expected ? $this->books_seen / $this->books_expected : null;
    }

    public function scopeAccessibleBy(Builder $query, ?User $actor): Builder
    {
        if (! $actor) {
            return $query->whereRaw('1 = 0');
        }

        if ($actor->hasNationalAccess() || $actor->hasRole('Admin')) {
            return $query;
        }

        return $query->whereIn('organization_id', Organization::query()->accessibleBy($actor)->select('id'));
    }
}
