<?php

namespace App\Http\Livewire\ManualTracking\Stock;

use App\Models\ManualTracking\ManualBook;
use App\Models\ManualTracking\ManualStockBalance;
use App\Models\Organization;
use App\Models\User;
use App\Services\ManualTracking\ManualStockService;
use App\Services\ManualTracking\ManualTrackingException;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class ManualStockIndex extends Component
{
    use WithPagination;

    public $organizationFilter = '';
    public $bookFilter = '';

    // Opening stock form
    public $showOpeningForm = false;
    public $opening = [
        'organization_id' => '',
        'manual_book_id' => '',
        'full_packages' => 0,
        'partial_package_books' => 0,
        'partial_teacher_guides' => 0,
        'notes' => '',
    ];

    public function updatingOrganizationFilter()
    {
        $this->resetPage();
    }

    public function updatingBookFilter()
    {
        $this->resetPage();
    }

    public function render()
    {
        /** @var User $actor */
        $actor = Auth::user();

        $balances = ManualStockBalance::query()
            ->accessibleBy($actor)
            ->selectRaw("organization_id, manual_book_id,
                SUM(CASE WHEN edition = 'student' THEN on_hand ELSE 0 END) as student_on_hand,
                SUM(CASE WHEN edition = 'teacher' THEN on_hand ELSE 0 END) as teacher_on_hand,
                SUM(CASE WHEN edition = 'student' THEN in_transit_out ELSE 0 END) as student_in_transit,
                SUM(CASE WHEN edition = 'teacher' THEN in_transit_out ELSE 0 END) as teacher_in_transit")
            ->when($this->organizationFilter, fn ($query) => $query->where('organization_id', $this->organizationFilter))
            ->when($this->bookFilter, fn ($query) => $query->where('manual_book_id', $this->bookFilter))
            ->groupBy('organization_id', 'manual_book_id')
            ->havingRaw('SUM(on_hand) + SUM(in_transit_out) > 0')
            ->with(['organization:id,name', 'book:id,title,students_per_package'])
            ->orderBy('organization_id')
            ->orderBy('manual_book_id')
            ->paginate(15);

        return view('livewire.manual-tracking.stock.manual-stock-index', [
            'balances' => $balances,
            'organizations' => Organization::query()->accessibleBy($actor)->orderBy('name')->get(['id', 'name']),
            'books' => ManualBook::query()->orderBy('title')->get(['id', 'title', 'has_teacher_guide', 'students_per_package']),
            'canRecordOpening' => $actor->hasNationalAccess() || $actor->hasRole('Admin')
                || $actor->effectiveAccessLevel() === User::ACCESS_LEVEL_REGION,
            'openingCopies' => $this->openingCopies(),
        ])->extends('main.manual-tracking.index');
    }

    public function recordOpeningStock(ManualStockService $stock)
    {
        $this->validate([
            'opening.organization_id' => 'required|exists:organizations,id',
            'opening.manual_book_id' => 'required|exists:manual_books,id',
            'opening.full_packages' => 'required|integer|min:0',
            'opening.partial_package_books' => 'required|integer|min:0',
            'opening.partial_teacher_guides' => 'required|integer|min:0',
            'opening.notes' => 'nullable|string|max:500',
        ], [], [
            'opening.organization_id' => 'organization',
            'opening.manual_book_id' => 'title',
        ]);

        try {
            $stock->recordOpeningStock(
                Auth::user(),
                Organization::query()->findOrFail($this->opening['organization_id']),
                ManualBook::query()->findOrFail($this->opening['manual_book_id']),
                $this->openingCopies(),
                $this->opening['notes'] ?: null
            );
        } catch (ManualTrackingException $exception) {
            return $this->alert('error', $exception->getMessage());
        }

        $this->reset('opening', 'showOpeningForm');
        $this->alert('success', 'Opening stock recorded.');
    }

    /**
     * @return array{student: int, teacher: int}
     */
    private function openingCopies(): array
    {
        $book = $this->opening['manual_book_id'] ? ManualBook::query()->find($this->opening['manual_book_id']) : null;

        return $book
            ? $book->copiesFor((int) $this->opening['full_packages'], (int) $this->opening['partial_package_books'], (int) $this->opening['partial_teacher_guides'])
            : [ManualBook::EDITION_STUDENT => 0, ManualBook::EDITION_TEACHER => 0];
    }

    private function alert(string $type, string $message): void
    {
        $this->dispatchBrowserEvent('alert', ['type' => $type, 'message' => $message]);
    }
}
