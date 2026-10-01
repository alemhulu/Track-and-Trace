<?php

namespace App\Http\Livewire\ManualTracking\Distribution;

use App\Models\Country;
use App\Models\ManualTracking\ManualAudit;
use App\Models\ManualTracking\ManualBook;
use App\Models\ManualTracking\ManualBookPackage;
use App\Models\ManualTracking\ManualDistribution;
use App\Models\ManualTracking\ManualDistributionLine;
use App\Models\ManualTracking\ManualStockLedger;
use App\Models\Organization;
use App\Models\Region;
use App\Models\Woreda;
use App\Models\Zone;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Component;

class AddManualDistribution extends Component
{
    public $books = [];
    public $packages = [];
    public $organizations = [];
    public $countries = [];
    public $regions = [];
    public $zones = [];
    public $woredas = [];

    public $manual_book_id;
    public $manual_book_package_id;
    public $quantity;
    public $remarks;

    public $organization_id;
    public $country_id;
    public $region_id;
    public $zone_id;
    public $woreda_id;

    public $destination_organization_id;
    public $destination_country_id;
    public $destination_region_id;
    public $destination_zone_id;
    public $destination_woreda_id;

    protected $rules = [
        'manual_book_id' => 'required|exists:manual_books,id',
        'manual_book_package_id' => 'nullable|exists:manual_book_packages,id',
        'quantity' => 'required|integer|min:1',
        'organization_id' => 'nullable|exists:organizations,id',
        'country_id' => 'nullable|exists:countries,id',
        'region_id' => 'nullable|exists:regions,id',
        'zone_id' => 'nullable|exists:zones,id',
        'woreda_id' => 'nullable|exists:woredas,id',
        'destination_organization_id' => 'nullable|exists:organizations,id',
        'destination_country_id' => 'nullable|exists:countries,id',
        'destination_region_id' => 'nullable|exists:regions,id',
        'destination_zone_id' => 'nullable|exists:zones,id',
        'destination_woreda_id' => 'nullable|exists:woredas,id',
        'remarks' => 'nullable|string|max:1000',
    ];

    public function mount()
    {
        $this->books = ManualBook::query()->orderBy('title')->get();
        $this->organizations = Organization::query()->orderBy('name')->get(['id', 'name']);
        $this->countries = Country::query()->orderBy('name')->get(['id', 'name']);
        $this->regions = Region::query()->orderBy('name')->get(['id', 'name']);
        $this->zones = Zone::query()->orderBy('name')->get(['id', 'name']);
        $this->woredas = Woreda::query()->orderBy('name')->get(['id', 'name']);
    }

    public function render()
    {
        return view('livewire.manual-tracking.distribution.add-manual-distribution')->extends('main.manual-tracking.index');
    }

    public function updatedManualBookId($bookId)
    {
        $this->manual_book_package_id = null;
        $this->packages = ManualBookPackage::query()
            ->where('manual_book_id', $bookId)
            ->orderBy('package_code')
            ->get();
    }

    public function saveDistribution()
    {
        try {
            $data = $this->validate();
            $actor = Auth::user();

            if (! $actor) {
                return $this->alertError('Authentication required.');
            }

            DB::transaction(function () use ($actor, $data): void {
                $package = null;
                $book = ManualBook::query()->findOrFail($data['manual_book_id']);

                if (! empty($data['manual_book_package_id'])) {
                    $package = ManualBookPackage::query()->findOrFail($data['manual_book_package_id']);
                    if ((int) $package->current_balance < (int) $data['quantity']) {
                        throw new \RuntimeException('Insufficient package balance for this distribution.');
                    }
                } elseif ((int) $book->total_copies < (int) $data['quantity']) {
                    throw new \RuntimeException('Insufficient book balance for this distribution.');
                }

                $reference = 'MD-' . now()->format('YmdHis') . '-' . strtoupper(Str::random(4));

                $distribution = ManualDistribution::query()->create([
                    'reference' => $reference,
                    'distributed_by' => $actor->id,
                    'organization_id' => $data['organization_id'] ?? $actor->organization_id,
                    'country_id' => $data['country_id'] ?? $actor->country_id,
                    'region_id' => $data['region_id'] ?? $actor->region_id,
                    'zone_id' => $data['zone_id'] ?? $actor->zone_id,
                    'woreda_id' => $data['woreda_id'] ?? $actor->woreda_id,
                    'destination_organization_id' => $data['destination_organization_id'] ?? null,
                    'destination_country_id' => $data['destination_country_id'] ?? null,
                    'destination_region_id' => $data['destination_region_id'] ?? null,
                    'destination_zone_id' => $data['destination_zone_id'] ?? null,
                    'destination_woreda_id' => $data['destination_woreda_id'] ?? null,
                    'distributed_at' => now(),
                    'remarks' => $data['remarks'] ?? null,
                ]);

                $balanceBefore = $package ? (int) $package->current_balance : (int) $book->total_copies;
                $balanceAfter = max($balanceBefore - (int) $data['quantity'], 0);

                ManualDistributionLine::query()->create([
                    'manual_distribution_id' => $distribution->id,
                    'manual_book_id' => $book->id,
                    'manual_book_package_id' => $package?->id,
                    'quantity' => (int) $data['quantity'],
                    'source_balance_before' => $balanceBefore,
                    'source_balance_after' => $balanceAfter,
                ]);

                if ($package) {
                    $package->update(['current_balance' => $balanceAfter]);
                }

                $book->update([
                    'total_copies' => max((int) $book->total_copies - (int) $data['quantity'], 0),
                ]);

                ManualStockLedger::query()->create([
                    'manual_book_id' => $book->id,
                    'manual_book_package_id' => $package?->id,
                    'movement_type' => 'distribution',
                    'movement_qty' => -1 * (int) $data['quantity'],
                    'balance_after' => $balanceAfter,
                    'ref_type' => ManualDistribution::class,
                    'ref_id' => $distribution->id,
                    'acted_by' => $actor->id,
                    'organization_id' => $distribution->organization_id,
                    'country_id' => $distribution->country_id,
                    'region_id' => $distribution->region_id,
                    'zone_id' => $distribution->zone_id,
                    'woreda_id' => $distribution->woreda_id,
                    'notes' => $distribution->remarks,
                ]);

                ManualAudit::query()->create([
                    'user_id' => $actor->id,
                    'action' => 'manual_distribution_created',
                    'auditable_type' => ManualDistribution::class,
                    'auditable_id' => $distribution->id,
                    'meta' => [
                        'manual_book_id' => $book->id,
                        'manual_book_package_id' => $package?->id,
                        'quantity' => (int) $data['quantity'],
                        'reference' => $reference,
                    ],
                ]);
            });
        } catch (\Throwable $exception) {
            return $this->alertError($exception->getMessage());
        }

        $this->resetForm();
        $this->alertSuccess('Manual distribution recorded successfully.');
        return redirect()->route('manual-tracking.distribution.list');
    }

    private function resetForm(): void
    {
        $this->manual_book_id = null;
        $this->manual_book_package_id = null;
        $this->quantity = null;
        $this->remarks = null;
        $this->organization_id = null;
        $this->country_id = null;
        $this->region_id = null;
        $this->zone_id = null;
        $this->woreda_id = null;
        $this->destination_organization_id = null;
        $this->destination_country_id = null;
        $this->destination_region_id = null;
        $this->destination_zone_id = null;
        $this->destination_woreda_id = null;
        $this->packages = [];
    }

    private function alertError($message)
    {
        $this->dispatchBrowserEvent('alert', [
            'type' => 'error',
            'message' => $message,
        ]);
    }

    private function alertSuccess($message)
    {
        $this->dispatchBrowserEvent('alert', [
            'type' => 'success',
            'message' => $message,
        ]);
    }
}
