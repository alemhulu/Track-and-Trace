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

    /**
     * The code a scan names, or null when it names none or several titles. Language editions
     * of one subject and grade can share a QR text; the code number under the QR tells them apart.
     */
    public static function findByScan(string $scanned): ?self
    {
        $matches = self::matchingScan($scanned);

        return $matches->pluck('manual_book_id')->unique()->count() === 1 ? $matches->first() : null;
    }

    /**
     * Every code a scan could be: the one whose printed code number it is, otherwise all that
     * carry its QR text.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, self>
     */
    public static function matchingScan(string $scanned)
    {
        $byNumber = self::query()->where('print_code', trim($scanned))->get();

        if ($byNumber->isNotEmpty()) {
            return $byNumber;
        }

        $byText = self::query()->where('normalized_payload', self::normalize($scanned))->orderBy('id')->get();
        if ($byText->isNotEmpty() || self::compact($scanned) === '') {
            return $byText;
        }

        // Some printed QRs drop a space ("Environmental ScienceTeacher guide"): ignore spacing last
        return self::query()
            ->whereRaw("REPLACE(normalized_payload, ' ', '') = ?", [self::compact($scanned)])
            ->orderBy('id')
            ->get();
    }

    public static function compact(?string $payload): string
    {
        return str_replace(' ', '', self::normalize($payload));
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(ManualBook::class, 'manual_book_id');
    }
}
