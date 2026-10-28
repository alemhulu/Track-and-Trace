<?php

namespace App\Services\ManualTracking;

use App\Models\ManualTracking\ManualAudit;
use App\Models\ManualTracking\ManualBook;
use App\Models\ManualTracking\ManualStockBalance;
use App\Models\ManualTracking\ManualStockLedger;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * The only writer of manual stock. Every change is a ledger entry for the holding
 * organization, and the holder's balance moves with it inside the same lock.
 */
class ManualStockService
{
    public const MOVEMENT_OPENING = 'opening';
    public const MOVEMENT_DISPATCH = 'dispatch';
    public const MOVEMENT_RECEIVE = 'receive';
    public const MOVEMENT_FOUND = 'found';
    public const MOVEMENT_ADJUSTMENT = 'adjustment';
    public const MOVEMENT_ISSUE_STUDENT = 'issue_to_student';
    public const MOVEMENT_STUDENT_RETURN = 'student_return';
    public const MOVEMENT_ISSUE_TEACHER = 'issue_to_teacher';
    public const MOVEMENT_TEACHER_RETURN = 'teacher_return';

    /**
     * Must run inside a database transaction.
     *
     * @param  array{actor?: ?User, shipment_id?: ?int, line_id?: ?int, notes?: ?string, client_uuid?: ?string}  $context
     */
    public function move(Organization $holder, ManualBook $book, string $edition, int $quantity, string $type, array $context = []): ManualStockLedger
    {
        if ($quantity === 0) {
            throw new \InvalidArgumentException('A stock movement needs a non-zero quantity.');
        }

        $balance = $this->lockedBalance($holder, $book, $edition);
        $onHand = $balance->on_hand + $quantity;

        if ($onHand < 0) {
            throw new ManualTrackingException(sprintf(
                '%s holds only %s %s of %s; cannot remove %s.',
                $holder->name,
                number_format($balance->on_hand),
                ManualBook::EDITIONS[$edition] ?? $edition,
                $book->title,
                number_format(-$quantity)
            ));
        }

        $balance->update(['on_hand' => $onHand]);

        return ManualStockLedger::query()->create([
            'manual_book_id' => $book->id,
            'edition' => $edition,
            'movement_type' => $type,
            'movement_qty' => $quantity,
            'balance_after' => $onHand,
            'ref_type' => isset($context['shipment_id']) ? \App\Models\ManualTracking\ManualDistribution::class : null,
            'ref_id' => $context['shipment_id'] ?? null,
            'manual_distribution_line_id' => $context['line_id'] ?? null,
            'acted_by' => isset($context['actor']) ? $context['actor']->id : null,
            'organization_id' => $holder->id,
            'country_id' => $holder->country_id,
            'region_id' => $holder->region_id,
            'zone_id' => $holder->zone_id,
            'woreda_id' => $holder->woreda_id,
            'notes' => $context['notes'] ?? null,
            'client_uuid' => $context['client_uuid'] ?? null,
            // Offline work is synced later; it is recorded at the time it actually happened
            'occurred_at' => $context['occurred_at'] ?? now(),
        ]);
    }

    /**
     * Must run inside a database transaction.
     */
    public function changeInTransit(Organization $holder, ManualBook $book, string $edition, int $delta): void
    {
        $balance = $this->lockedBalance($holder, $book, $edition);
        $inTransit = $balance->in_transit_out + $delta;

        if ($inTransit < 0) {
            throw new \LogicException("In-transit stock for {$holder->name} would become negative.");
        }

        $balance->update(['in_transit_out' => $inTransit]);
    }

    /**
     * Books already sitting in a warehouse before the system was used. Creates stock from
     * nothing, so only national and regional users may record it.
     *
     * @param  array{student?: int, teacher?: int}  $copies
     */
    public function recordOpeningStock(User $actor, Organization $holder, ManualBook $book, array $copies, ?string $notes = null): void
    {
        $level = $actor->effectiveAccessLevel();
        $mayRecord = $actor->hasNationalAccess() || $actor->hasRole('Admin') || $level === User::ACCESS_LEVEL_REGION;

        if (! $mayRecord || ! $this->canActFor($actor, $holder)) {
            throw new ManualTrackingException('Only national or regional users can record opening stock for organizations in their area.');
        }

        $copies = array_filter($copies, fn ($quantity) => (int) $quantity > 0);
        if (empty($copies)) {
            throw new ManualTrackingException('Enter at least one copy of opening stock.');
        }

        DB::transaction(function () use ($actor, $holder, $book, $copies, $notes): void {
            foreach ($copies as $edition => $quantity) {
                $this->move($holder, $book, $edition, (int) $quantity, self::MOVEMENT_OPENING, [
                    'actor' => $actor,
                    'notes' => $notes ?: 'Opening stock',
                ]);
            }

            ManualAudit::query()->create([
                'user_id' => $actor->id,
                'action' => 'opening_stock_recorded',
                'auditable_type' => Organization::class,
                'auditable_id' => $holder->id,
                'meta' => ['manual_book_id' => $book->id, 'copies' => $copies],
            ]);
        });
    }

    public function canActFor(User $actor, Organization $organization): bool
    {
        if ($actor->hasNationalAccess() || $actor->hasRole('Admin')) {
            return true;
        }

        return Organization::query()->accessibleBy($actor)->whereKey($organization->id)->exists();
    }

    private function lockedBalance(Organization $holder, ManualBook $book, string $edition): ManualStockBalance
    {
        $keys = ['organization_id' => $holder->id, 'manual_book_id' => $book->id, 'edition' => $edition];

        return ManualStockBalance::query()->where($keys)->lockForUpdate()->first()
            ?? ManualStockBalance::query()->create($keys + ['on_hand' => 0, 'in_transit_out' => 0]);
    }
}
