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
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Livewire\Component;

class AddManualDistribution extends Component
{
    public $books = [];
    public $packages = [];
    public $organizations = [];
    public $countries = [];
    public $sourceRegions = [];
    public $sourceZones = [];
    public $sourceWoredas = [];
    public $destinationRegions = [];
    public $destinationZones = [];
    public $destinationWoredas = [];

    public $manual_book_id;
    public $manual_book_package_id;
    public $quantity;
    public $remarks;
    public $selectedBookTotalCopies = 0;
    public $selectedPackageAvailable = 0;

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
        $this->sourceRegions = [];
        $this->sourceZones = [];
        $this->sourceWoredas = [];
        $this->destinationRegions = [];
        $this->destinationZones = [];
        $this->destinationWoredas = [];
    }

    public function render()
    {
        return view('livewire.manual-tracking.distribution.add-manual-distribution')->extends('main.manual-tracking.index');
    }

    public function updatedManualBookId($bookId)
    {
        $this->manual_book_package_id = null;
        $this->selectedPackageAvailable = 0;

        $book = ManualBook::query()->find($bookId);
        $this->selectedBookTotalCopies = $book ? (int) $book->total_copies : 0;

        $this->packages = ManualBookPackage::query()
            ->where('manual_book_id', $bookId)
            ->orderBy('package_code')
            ->get();
    }

    public function updatedManualBookPackageId($packageId): void
    {
        $package = $packageId ? ManualBookPackage::query()->find($packageId) : null;
        $this->selectedPackageAvailable = $package ? (int) $package->current_balance : 0;
    }

    public function updatedOrganizationId($organizationId)
    {
        $organization = $organizationId ? Organization::query()->find($organizationId) : null;

        $this->country_id = $organization?->country_id;
        $this->region_id = $organization?->region_id;
        $this->zone_id = $organization?->zone_id;
        $this->woreda_id = $organization?->woreda_id;

        $this->hydrateSourceOptions();
    }

    public function updatedCountryId($countryId)
    {
        $this->region_id = null;
        $this->zone_id = null;
        $this->woreda_id = null;

        $this->sourceRegions = $countryId
            ? Region::query()->where('country_id', $countryId)->orderBy('name')->get(['id', 'name'])
            : [];
        $this->sourceZones = [];
        $this->sourceWoredas = [];
    }

    public function updatedRegionId($regionId)
    {
        $region = $regionId ? Region::query()->find($regionId) : null;

        $this->country_id = $region?->country_id;
        $this->zone_id = null;
        $this->woreda_id = null;

        $this->sourceRegions = $this->country_id
            ? Region::query()->where('country_id', $this->country_id)->orderBy('name')->get(['id', 'name'])
            : [];
        $this->sourceZones = $regionId
            ? Zone::query()->where('region_id', $regionId)->orderBy('name')->get(['id', 'name'])
            : [];
        $this->sourceWoredas = [];
    }

    public function updatedZoneId($zoneId)
    {
        $zone = $zoneId ? Zone::query()->find($zoneId) : null;

        if ($zone) {
            $this->region_id = $zone->region_id;
            $this->country_id = $zone->country_id;
        }

        $this->sourceRegions = $this->country_id
            ? Region::query()->where('country_id', $this->country_id)->orderBy('name')->get(['id', 'name'])
            : [];
        $this->sourceZones = $this->region_id
            ? Zone::query()->where('region_id', $this->region_id)->orderBy('name')->get(['id', 'name'])
            : [];
        $this->woreda_id = null;
        $this->sourceWoredas = $zoneId
            ? Woreda::query()->where('zone_id', $zoneId)->orderBy('name')->get(['id', 'name'])
            : [];
    }

    public function updatedWoredaId($woredaId)
    {
        $woreda = $woredaId ? Woreda::query()->find($woredaId) : null;
        if (! $woreda) {
            return;
        }

        $this->zone_id = $woreda->zone_id;
        $this->region_id = $woreda->region_id;
        $this->country_id = $woreda->country_id;
        $this->hydrateSourceOptions();
    }

    public function updatedDestinationOrganizationId($organizationId)
    {
        $organization = $organizationId ? Organization::query()->find($organizationId) : null;

        $this->destination_country_id = $organization?->country_id;
        $this->destination_region_id = $organization?->region_id;
        $this->destination_zone_id = $organization?->zone_id;
        $this->destination_woreda_id = $organization?->woreda_id;

        $this->hydrateDestinationOptions();
    }

    public function updatedDestinationCountryId($countryId)
    {
        $this->destination_region_id = null;
        $this->destination_zone_id = null;
        $this->destination_woreda_id = null;

        $this->destinationRegions = $countryId
            ? Region::query()->where('country_id', $countryId)->orderBy('name')->get(['id', 'name'])
            : [];
        $this->destinationZones = [];
        $this->destinationWoredas = [];
    }

    public function updatedDestinationRegionId($regionId)
    {
        $region = $regionId ? Region::query()->find($regionId) : null;

        $this->destination_country_id = $region?->country_id;
        $this->destination_zone_id = null;
        $this->destination_woreda_id = null;

        $this->destinationRegions = $this->destination_country_id
            ? Region::query()->where('country_id', $this->destination_country_id)->orderBy('name')->get(['id', 'name'])
            : [];
        $this->destinationZones = $regionId
            ? Zone::query()->where('region_id', $regionId)->orderBy('name')->get(['id', 'name'])
            : [];
        $this->destinationWoredas = [];
    }

    public function updatedDestinationZoneId($zoneId)
    {
        $zone = $zoneId ? Zone::query()->find($zoneId) : null;

        if ($zone) {
            $this->destination_region_id = $zone->region_id;
            $this->destination_country_id = $zone->country_id;
        }

        $this->destinationRegions = $this->destination_country_id
            ? Region::query()->where('country_id', $this->destination_country_id)->orderBy('name')->get(['id', 'name'])
            : [];
        $this->destinationZones = $this->destination_region_id
            ? Zone::query()->where('region_id', $this->destination_region_id)->orderBy('name')->get(['id', 'name'])
            : [];
        $this->destination_woreda_id = null;
        $this->destinationWoredas = $zoneId
            ? Woreda::query()->where('zone_id', $zoneId)->orderBy('name')->get(['id', 'name'])
            : [];
    }

    public function updatedDestinationWoredaId($woredaId)
    {
        $woreda = $woredaId ? Woreda::query()->find($woredaId) : null;
        if (! $woreda) {
            return;
        }

        $this->destination_zone_id = $woreda->zone_id;
        $this->destination_region_id = $woreda->region_id;
        $this->destination_country_id = $woreda->country_id;
        $this->hydrateDestinationOptions();
    }

    private function hydrateSourceOptions(): void
    {
        $this->sourceRegions = $this->country_id
            ? Region::query()->where('country_id', $this->country_id)->orderBy('name')->get(['id', 'name'])
            : [];

        $this->sourceZones = $this->region_id
            ? Zone::query()->where('region_id', $this->region_id)->orderBy('name')->get(['id', 'name'])
            : [];

        $this->sourceWoredas = $this->zone_id
            ? Woreda::query()->where('zone_id', $this->zone_id)->orderBy('name')->get(['id', 'name'])
            : [];
    }

    private function hydrateDestinationOptions(): void
    {
        $this->destinationRegions = $this->destination_country_id
            ? Region::query()->where('country_id', $this->destination_country_id)->orderBy('name')->get(['id', 'name'])
            : [];

        $this->destinationZones = $this->destination_region_id
            ? Zone::query()->where('region_id', $this->destination_region_id)->orderBy('name')->get(['id', 'name'])
            : [];

        $this->destinationWoredas = $this->destination_zone_id
            ? Woreda::query()->where('zone_id', $this->destination_zone_id)->orderBy('name')->get(['id', 'name'])
            : [];
    }

    public function saveDistribution()
    {
        try {
            $actor = Auth::user();
            $data = $this->validate();

            if (! $actor) {
                return $this->alertError('Authentication required.');
            }

            if (Gate::denies('create', ManualDistribution::class)) {
                return $this->alertError('You are not authorized to create manual distributions.');
            }

            $this->validateDistributionQuantity($data);
            $data = $this->validateAndNormalizeHierarchy($data, $actor);

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
                    'organization_id' => $data['organization_id'] ?? null,
                    'country_id' => $data['country_id'] ?? null,
                    'region_id' => $data['region_id'] ?? null,
                    'zone_id' => $data['zone_id'] ?? null,
                    'woreda_id' => $data['woreda_id'] ?? null,
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

    public function validateDistributionQuantity(array $data): void
    {
        $bookId = (int) ($data['manual_book_id'] ?? $this->manual_book_id ?? 0);
        $quantity = (int) ($data['quantity'] ?? $this->quantity ?? 0);
        $packageId = $data['manual_book_package_id'] ?? $this->manual_book_package_id ?? null;

        if ($bookId <= 0) {
            throw new \RuntimeException('A book must be selected before recording a distribution.');
        }

        $book = ManualBook::query()->find($bookId);
        if (! $book) {
            throw new \RuntimeException('Selected book not found.');
        }

        $this->selectedBookTotalCopies = (int) $book->total_copies;

        if ($packageId) {
            $package = ManualBookPackage::query()->find($packageId);
            if (! $package) {
                throw new \RuntimeException('Selected package is invalid.');
            }

            if ((int) $package->manual_book_id !== $bookId) {
                throw new \RuntimeException('Selected package does not belong to the chosen book.');
            }

            $this->selectedPackageAvailable = (int) $package->current_balance;

            if ($quantity > (int) $package->current_balance) {
                throw new \RuntimeException('Quantity ' . $quantity . ' exceeds the package balance of ' . $package->current_balance . '.');
            }
        } else {
            $this->selectedPackageAvailable = 0;
        }

        if ($quantity > (int) $book->total_copies) {
            throw new \RuntimeException('Quantity ' . $quantity . ' exceeds the available book copies (' . $book->total_copies . ').');
        }
    }

    private function validateAndNormalizeHierarchy(array $data, $actor): array
    {
        $source = [
            'organization_id' => $data['organization_id'] ?? $actor->organization_id ?? null,
            'country_id' => $data['country_id'] ?? $actor->country_id ?? null,
            'region_id' => $data['region_id'] ?? $actor->region_id ?? null,
            'zone_id' => $data['zone_id'] ?? $actor->zone_id ?? null,
            'woreda_id' => $data['woreda_id'] ?? $actor->woreda_id ?? null,
        ];

        $destination = [
            'organization_id' => $data['destination_organization_id'] ?? null,
            'country_id' => $data['destination_country_id'] ?? null,
            'region_id' => $data['destination_region_id'] ?? null,
            'zone_id' => $data['destination_zone_id'] ?? null,
            'woreda_id' => $data['destination_woreda_id'] ?? null,
        ];

        if (! $this->hasAnyLocationScope($source)) {
            throw new \RuntimeException('Source location is required. Select organization or hierarchy details.');
        }

        if (! $this->hasAnyLocationScope($destination)) {
            throw new \RuntimeException('Destination location is required. Select destination organization or hierarchy details.');
        }

        $source = $this->resolveHierarchyChain($source, 'source');
        $destination = $this->resolveHierarchyChain($destination, 'destination');

        $source = $this->alignWithOrganization($source, 'source');
        $destination = $this->alignWithOrganization($destination, 'destination');

        $data['organization_id'] = $source['organization_id'];
        $data['country_id'] = $source['country_id'];
        $data['region_id'] = $source['region_id'];
        $data['zone_id'] = $source['zone_id'];
        $data['woreda_id'] = $source['woreda_id'];

        $data['destination_organization_id'] = $destination['organization_id'];
        $data['destination_country_id'] = $destination['country_id'];
        $data['destination_region_id'] = $destination['region_id'];
        $data['destination_zone_id'] = $destination['zone_id'];
        $data['destination_woreda_id'] = $destination['woreda_id'];

        return $data;
    }

    private function hasAnyLocationScope(array $scope): bool
    {
        return ! empty($scope['organization_id'])
            || ! empty($scope['region_id'])
            || ! empty($scope['zone_id'])
            || ! empty($scope['woreda_id']);
    }

    private function resolveHierarchyChain(array $scope, string $label): array
    {
        if (! empty($scope['woreda_id'])) {
            $woreda = Woreda::query()->find($scope['woreda_id']);
            if (! $woreda) {
                throw new \RuntimeException(ucfirst($label) . ' woreda is invalid.');
            }

            $scope['zone_id'] = $scope['zone_id'] ?? $woreda->zone_id;
            $scope['region_id'] = $scope['region_id'] ?? $woreda->region_id;
            $scope['country_id'] = $scope['country_id'] ?? $woreda->country_id;

            if (! empty($scope['zone_id']) && (int) $scope['zone_id'] !== (int) $woreda->zone_id) {
                throw new \RuntimeException(ucfirst($label) . ' hierarchy mismatch: woreda does not belong to selected zone.');
            }
            if (! empty($scope['region_id']) && (int) $scope['region_id'] !== (int) $woreda->region_id) {
                throw new \RuntimeException(ucfirst($label) . ' hierarchy mismatch: woreda does not belong to selected region.');
            }
            if (! empty($scope['country_id']) && (int) $scope['country_id'] !== (int) $woreda->country_id) {
                throw new \RuntimeException(ucfirst($label) . ' hierarchy mismatch: woreda does not belong to selected country.');
            }
        }

        if (! empty($scope['zone_id'])) {
            $zone = Zone::query()->find($scope['zone_id']);
            if (! $zone) {
                throw new \RuntimeException(ucfirst($label) . ' zone is invalid.');
            }

            $scope['region_id'] = $scope['region_id'] ?? $zone->region_id;
            $scope['country_id'] = $scope['country_id'] ?? $zone->country_id;

            if (! empty($scope['region_id']) && (int) $scope['region_id'] !== (int) $zone->region_id) {
                throw new \RuntimeException(ucfirst($label) . ' hierarchy mismatch: zone does not belong to selected region.');
            }
            if (! empty($scope['country_id']) && (int) $scope['country_id'] !== (int) $zone->country_id) {
                throw new \RuntimeException(ucfirst($label) . ' hierarchy mismatch: zone does not belong to selected country.');
            }
        }

        if (! empty($scope['region_id'])) {
            $region = Region::query()->find($scope['region_id']);
            if (! $region) {
                throw new \RuntimeException(ucfirst($label) . ' region is invalid.');
            }

            $scope['country_id'] = $scope['country_id'] ?? $region->country_id;

            if (! empty($scope['country_id']) && (int) $scope['country_id'] !== (int) $region->country_id) {
                throw new \RuntimeException(ucfirst($label) . ' hierarchy mismatch: region does not belong to selected country.');
            }
        }

        return $scope;
    }

    private function alignWithOrganization(array $scope, string $label): array
    {
        if (empty($scope['organization_id'])) {
            return $scope;
        }

        $organization = Organization::query()->find($scope['organization_id']);
        if (! $organization) {
            throw new \RuntimeException(ucfirst($label) . ' organization is invalid.');
        }

        foreach (['country_id', 'region_id', 'zone_id', 'woreda_id'] as $field) {
            $orgValue = $organization->{$field};

            if (empty($orgValue)) {
                continue;
            }

            if (empty($scope[$field])) {
                $scope[$field] = $orgValue;
                continue;
            }

            if ((int) $scope[$field] !== (int) $orgValue) {
                $name = str_replace('_id', '', $field);
                throw new \RuntimeException(ucfirst($label) . ' mismatch: selected ' . $name . ' does not match organization location.');
            }
        }

        return $scope;
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
        $this->sourceRegions = [];
        $this->sourceZones = [];
        $this->sourceWoredas = [];
        $this->destinationRegions = [];
        $this->destinationZones = [];
        $this->destinationWoredas = [];
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
