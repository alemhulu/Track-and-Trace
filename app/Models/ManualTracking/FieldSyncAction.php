<?php

namespace App\Models\ManualTracking;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One offline action as received from the field app, with what became of it.
 */
class FieldSyncAction extends ManualBaseModel
{
    public const STATUS_APPLIED = 'applied';
    public const STATUS_REJECTED = 'rejected';

    public const TYPE_RECEIVE_SHIPMENT = 'receive_shipment';
    public const TYPE_ISSUE_BOOKS = 'issue_books';
    public const TYPE_RETURN_BOOK = 'return_book';

    public const TYPES = [self::TYPE_RECEIVE_SHIPMENT, self::TYPE_ISSUE_BOOKS, self::TYPE_RETURN_BOOK];

    protected $casts = [
        'payload' => 'array',
        'recorded_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * @return array{uuid: string, status: string, message: ?string}
     */
    public function result(): array
    {
        return ['uuid' => $this->client_uuid, 'status' => $this->status, 'message' => $this->message];
    }
}
