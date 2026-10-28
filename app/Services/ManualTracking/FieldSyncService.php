<?php

namespace App\Services\ManualTracking;

use App\Models\ManualTracking\FieldSyncAction;
use App\Models\ManualTracking\ManualBook;
use App\Models\ManualTracking\ManualBookCode;
use App\Models\ManualTracking\ManualDistribution;
use App\Models\ManualTracking\ManualStockBalance;
use App\Models\Organization;
use App\Models\School\Student;
use App\Models\School\StudentBookIssue;
use App\Models\User;
use App\Models\Zone;
use App\Services\School\SchoolBookService;
use App\Support\AcademicYear;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * The offline field app: a data pack for one organization to work from without a
 * connection, and replay of the actions recorded meanwhile through the usual services.
 */
class FieldSyncService
{
    // Offline work older than this is refused rather than back-dated
    private const MAX_OFFLINE_DAYS = 60;

    public function __construct(
        private ManualStockService $stock,
        private ManualShipmentService $shipments,
        private SchoolBookService $schoolBooks,
    ) {
    }

    /**
     * Everything the app needs to receive shipments and, at a school, issue books offline.
     */
    public function pack(User $actor, Organization $organization): array
    {
        if (! $this->stock->canActFor($actor, $organization)) {
            throw new ManualTrackingException('You can only work offline for organizations in your area.');
        }

        $organization->loadMissing(['organizationType', 'zone']);
        $isSchool = ($organization->organizationType->name ?? null) === 'School';
        $year = AcademicYear::current();

        $titles = ManualBook::query()
            ->when($organization->region_id, fn ($query) => $query->where(fn ($match) => $match->whereNull('region_id')->orWhere('region_id', $organization->region_id)))
            ->with('codes:id,manual_book_id,edition,normalized_payload,print_code')
            ->orderBy('title')
            ->get(['id', 'title', 'grade_id', 'has_teacher_guide', 'students_per_package']);

        $shipments = ManualDistribution::query()
            ->where('destination_organization_id', $organization->id)
            ->where('status', ManualDistribution::STATUS_DISPATCHED)
            ->where('distributed_by', '!=', $actor->id)
            ->with(['organization:id,name', 'lines.book:id,title,has_teacher_guide'])
            ->orderBy('distributed_at')
            ->get();

        $students = $isSchool
            ? Student::query()->active()
                ->where('organization_id', $organization->id)
                ->where('academic_year', $year)
                ->with(['grade:id,name', 'heldBooks:id,student_id,manual_book_id'])
                ->orderBy('grade_id')->orderBy('section')->orderBy('name')
                ->get()
            : collect();

        return [
            'generated_at' => now()->toIso8601String(),
            'csrf' => csrf_token(),
            'organization' => [
                'id' => $organization->id,
                'name' => $organization->name,
                'is_school' => $isSchool,
                'zone_key' => $organization->zone ? ManualScanService::zoneKey($organization->zone->name) : null,
                'zone' => $organization->zone->name ?? null,
                'region_id' => $organization->region_id,
            ],
            'academic_year' => AcademicYear::label($year),
            'zones' => Zone::query()->get(['name', 'region_id'])->map(fn ($zone) => [
                'key' => ManualScanService::zoneKey($zone->name),
                'name' => $zone->name,
                'region_id' => $zone->region_id,
            ])->values(),
            'titles' => $titles->map(fn ($book) => [
                'id' => $book->id,
                'title' => $book->title,
                'grade_id' => $book->grade_id,
                'has_teacher_guide' => (bool) $book->has_teacher_guide,
                'per_package' => $book->students_per_package,
                'codes' => $book->codes->map(fn ($code) => [
                    'text' => $code->normalized_payload,
                    'print_code' => $code->print_code,
                    'edition' => $code->edition,
                ])->values(),
            ])->values(),
            'stock' => ManualStockBalance::query()
                ->where('organization_id', $organization->id)
                ->where('edition', ManualBook::EDITION_STUDENT)
                ->pluck('on_hand', 'manual_book_id'),
            'shipments' => $shipments->map(fn ($shipment) => [
                'id' => $shipment->id,
                'reference' => $shipment->reference,
                'from' => $shipment->organization->name ?? '—',
                'dispatched_at' => optional($shipment->distributed_at)->toDateString(),
                'expected_at' => optional($shipment->expected_at)->toDateString(),
                'lines' => $shipment->lines->map(fn ($line) => [
                    'id' => $line->id,
                    'title' => $line->book->title,
                    'has_teacher_guide' => (bool) $line->book->has_teacher_guide,
                    'student_books' => (int) $line->quantity,
                    'teacher_guides' => (int) $line->teacher_guides,
                    'packages' => $line->full_packages + ($line->partial_package_books || $line->partial_teacher_guides ? 1 : 0),
                ])->values(),
            ])->values(),
            'students' => $students->map(fn ($student) => [
                'id' => $student->id,
                'student_no' => $student->student_no,
                'name' => $student->name,
                'grade_id' => $student->grade_id,
                'grade' => $student->grade->name ?? '',
                'section' => $student->section,
                'held' => $student->heldBooks->pluck('manual_book_id')->values(),
            ])->values(),
        ];
    }

    /**
     * Apply one offline action. Sending an action again returns what happened the first time.
     *
     * @return array{uuid: string, status: string, message: ?string}
     */
    public function process(User $actor, array $action): array
    {
        $uuid = (string) ($action['uuid'] ?? '');
        if (! Str::isUuid($uuid)) {
            return ['uuid' => $uuid, 'status' => FieldSyncAction::STATUS_REJECTED, 'message' => 'The action has no valid id.'];
        }

        if ($existing = FieldSyncAction::query()->where('client_uuid', $uuid)->first()) {
            return (int) $existing->user_id === (int) $actor->id
                ? $existing->result()
                : ['uuid' => $uuid, 'status' => FieldSyncAction::STATUS_REJECTED, 'message' => 'This action was sent by another user.'];
        }

        $type = (string) ($action['type'] ?? '');
        $data = (array) ($action['data'] ?? []);
        $recordedAt = $this->recordedAt($action['recorded_at'] ?? null);

        try {
            return DB::transaction(function () use ($actor, $uuid, $type, $data, $recordedAt, $action): array {
                $row = FieldSyncAction::query()->create([
                    'client_uuid' => $uuid,
                    'user_id' => $actor->id,
                    'organization_id' => Organization::query()->whereKey($action['organization_id'] ?? 0)->value('id'),
                    'type' => Str::limit($type, 30, ''),
                    'payload' => $data,
                    'recorded_at' => $recordedAt ?? now(),
                    'status' => FieldSyncAction::STATUS_REJECTED,
                ]);

                try {
                    // Runs in a savepoint, so a refused action leaves no partial changes behind
                    $message = DB::transaction(fn () => $this->apply($actor, $type, $data, $recordedAt));
                    $row->update(['status' => FieldSyncAction::STATUS_APPLIED, 'message' => $message]);
                } catch (ManualTrackingException $exception) {
                    $row->update(['message' => Str::limit($exception->getMessage(), 500, '')]);
                } catch (ModelNotFoundException) {
                    $row->update(['message' => 'The record this action refers to no longer exists or is outside your area.']);
                }

                return $row->result();
            });
        } catch (QueryException $exception) {
            // Two syncs of the same queue raced; the other one recorded it
            if ($existing = FieldSyncAction::query()->where('client_uuid', $uuid)->first()) {
                return $existing->result();
            }

            throw $exception;
        }
    }

    private function apply(User $actor, string $type, array $data, ?Carbon $recordedAt): string
    {
        if ($recordedAt === null) {
            throw new ManualTrackingException('The time this was recorded is missing or more than ' . self::MAX_OFFLINE_DAYS . ' days old. Enter it again online.');
        }

        return match ($type) {
            FieldSyncAction::TYPE_RECEIVE_SHIPMENT => $this->receiveShipment($actor, $data, $recordedAt),
            FieldSyncAction::TYPE_ISSUE_BOOKS => $this->issueBooks($actor, $data, $recordedAt),
            FieldSyncAction::TYPE_RETURN_BOOK => $this->returnBook($actor, $data, $recordedAt),
            default => throw new ManualTrackingException('Unknown action.'),
        };
    }

    private function receiveShipment(User $actor, array $data, Carbon $recordedAt): string
    {
        $shipment = ManualDistribution::query()->accessibleBy($actor)->findOrFail((int) ($data['shipment_id'] ?? 0));

        $counts = collect((array) ($data['counts'] ?? []))->map(fn ($count) => collect((array) $count)
            ->only(['student_books', 'teacher_guides', 'damaged_quantity', 'damaged_teacher_guides'])
            ->map(fn ($value) => (int) $value)
            ->all())->all();

        $received = $this->shipments->receive(
            $actor,
            $shipment,
            $counts,
            isset($data['notes']) ? Str::limit(trim((string) $data['notes']), 1000, '') ?: null : null,
            $recordedAt->max($shipment->distributed_at ?? $recordedAt)
        );

        return $received->status === ManualDistribution::STATUS_RECEIVED
            ? "Shipment {$received->reference} received."
            : "Shipment {$received->reference} received with discrepancies.";
    }

    private function issueBooks(User $actor, array $data, Carbon $recordedAt): string
    {
        $student = Student::query()->with('school')->findOrFail((int) ($data['student_id'] ?? 0));
        $titleIds = array_map('intval', (array) ($data['title_ids'] ?? []));

        $count = $this->schoolBooks->issueToStudents($actor, $student->school, [$student->id => $titleIds], (string) ($data['condition'] ?? 'new'), $recordedAt);

        return $count
            ? "Issued {$count} " . ($count === 1 ? 'book' : 'books') . " to {$student->name}."
            : "{$student->name} already held these books.";
    }

    private function returnBook(User $actor, array $data, Carbon $recordedAt): string
    {
        $student = Student::query()->with('school')->findOrFail((int) ($data['student_id'] ?? 0));
        $this->schoolBooks->authorize($actor, $student->school);

        $issue = StudentBookIssue::query()
            ->where('student_id', $student->id)
            ->where('manual_book_id', (int) ($data['title_id'] ?? 0))
            ->whereNull('returned_at')
            ->first() ?? throw new ManualTrackingException("{$student->name} does not hold that book.");

        $this->schoolBooks->returnFromStudent($actor, $issue, (string) ($data['condition'] ?? 'good'), $recordedAt);

        return "Return from {$student->name} recorded.";
    }

    // The phone's clock is trusted only within reason: never in the future, never too old
    private function recordedAt($value): ?Carbon
    {
        try {
            // Phones send UTC; store in the app's zone like every other timestamp
            $at = Carbon::parse((string) $value)->setTimezone(config('app.timezone'));
        } catch (\Throwable) {
            return null;
        }

        if (! $value || $at->lt(now()->subDays(self::MAX_OFFLINE_DAYS))) {
            return null;
        }

        return $at->min(now());
    }
}
