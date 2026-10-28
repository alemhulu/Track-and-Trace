<?php

namespace App\Models\ManualTracking;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * Text printed in a book's QR code (e.g. "Oromia Grade 6 Mathematics Teacher guide")
 * and the human-readable code printed under it, mapped to a title edition.
 */
class ManualBookCode extends ManualBaseModel
{
    protected $fillable = [
        'manual_book_id',
        'edition',
        'payload',
        'normalized_payload',
        'print_code',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $code): void {
            $code->normalized_payload = self::normalize($code->payload);
        });
    }

    // Scanners differ in case and spacing; matching ignores both
    public static function normalize(?string $payload): string
    {
        return Str::lower(trim(preg_replace('/\s+/', ' ', (string) $payload)));
    }

    public static function findByScan(string $scanned): ?self
    {
        return self::query()
            ->where('normalized_payload', self::normalize($scanned))
            ->orWhere('print_code', trim($scanned))
            ->first();
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(ManualBook::class, 'manual_book_id');
    }
}
