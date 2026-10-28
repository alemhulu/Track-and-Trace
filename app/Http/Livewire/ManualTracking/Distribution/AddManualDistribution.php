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
use App\Models\User;
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
    public $destinationOrganizations = [];
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

    public ?int $actor_country_id = null;
    public ?int $actor_region_id = null;
    public ?int $actor_zone_id = null;
    public ?int $actor_woreda_id = null;
    public ?int $actor_organization_id = null;

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
        $actor = Auth::user();
        if (! $actor instanceof User) {
            abort(403);
        }

        $this->hydrateActorScope($actor);

        $this->books = ManualBook::query()->orderBy('title')->get();
        $this->organizations = Organization::query()
            ->accessibleBy($actor)
            ->orderBy('name')
            ->get(['id', 'name']);
        $this->countries = Country::query()->orderBy('name')->get(['id', 'name']);

        $defaultCountry = $this->actor_country_id ?? $this->defaultCountryId();
        $this->country_id = $this->normalizeNullableId($this->country_id) ?? $defaultCountry;
        $this->destination_country_id = $this->normalizeNullableId($this->destination_country_id) ?? $defaultCountry;
        $this->region_id = $this->normalizeNullableId($this->region_id) ?? $this->actor_region_id;
        $this->destination_region_id = $this->normalizeNullableId($this->destination_region_id) ?? $this->actor_region_id;
        $this->zone_id = $this->normalizeNullableId($this->zone_id) ?? $this->actor_zone_id;
        $this->destination_zone_id = $this->normalizeNullableId($this->destination_zone_id) ?? $this->actor_zone_id;
        $this->woreda_id = $this->normalizeNullableId($this->woreda_id) ?? $this->actor_woreda_id;
        $this->destination_woreda_id = $this->normalizeNullableId($this->destination_woreda_id) ?? $this->actor_woreda_id;
        $this->organization_id = $this->normalizeNullableId($this->organization_id) ?? $this->actor_organization_id;
        $this->destination_organization_id = $this->normalizeNullableId($this->destination_organization_id);

        $this->sourceRegions = [];
        $this->sourceZones = [];
        $this->sourceWoredas = [];
        $this->destinationRegions = [];
        $this->destinationZones = [];
        $this->destinationWoredas = [];

        $this->hydrateSourceOptions();
        $this->hydrateDestinationOptions();
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
        $organization = $organizationId
            ? Organization::query()->accessibleBy($this->currentActor())->find($organizationId)
            : null;

        $this->country_id = $organization?->country_id;
        $this->region_id = $organization?->region_id;
        $this->zone_id = $organization?->zone_id;
        $this->woreda_id = $organization?->woreda_id;

        $this->hydrateSourceOptions();
    }

    public function updatedCountryId($countryId)
    {
        $this->organization_id = null;
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
        $region = $regionId
            ? $this->applyActorScopeToRegionQuery(Region::query())->find($regionId)
            : null;

        $this->organization_id = null;
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
        $zone = $zoneId
            ? $this->applyActorScopeToZoneQuery(Zone::query())->find($zoneId)
            : null;

        $this->organization_id = null;

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
        $woreda = $woredaId
            ? $this->applyActorScopeToWoredaQuery(Woreda::query()->with('zone:id,region_id,country_id'))->find($woredaId)
            : null;
        if (! $woreda) {
            $this->organization_id = null;

            return;
        }

        $this->organization_id = null;
        $this->zone_id = $woreda->zone_id;
        $this->region_id = $woreda->zone?->region_id;
        $this->country_id = $woreda->zone?->country_id;
        $this->hydrateSourceOptions();
    }

    public function updatedDestinationOrganizationId($organizationId)
    {
        // Destinations are not limited to the actor's scope
        $organization = $organizationId
            ? Organization::query()->find($organizationId)
            : null;

        $this->destination_country_id = $organization?->country_id;
        $this->destination_region_id = $organization?->region_id;
        $this->destination_zone_id = $organization?->zone_id;
        $this->destination_woreda_id = $organization?->woreda_id;

        $this->hydrateDestinationOptions();
    }

    public function updatedDestinationCountryId($countryId)
    {
        $this->destination_organization_id = null;
        $this->destination_region_id = null;
        $this->destination_zone_id = null;
        $this->destination_woreda_id = null;

        $this->destinationRegions = $countryId
            ? Region::query()->where('country_id', $countryId)->orderBy('name')->get(['id', 'name'])
            : [];
        $this->destinationZones = [];
        $this->destinationWoredas = [];
        $this->hydrateDestinationOrganizations();
    }

    public function updatedDestinationRegionId($regionId)
    {
        $region = $regionId
            ? Region::query()->find($regionId)
            : null;

        $this->destination_organization_id = null;
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
        $this->hydrateDestinationOrganizations();
    }

    public function updatedDestinationZoneId($zoneId)
    {
        $zone = $zoneId
            ? Zone::query()->find($zoneId)
            : null;

        $this->destination_organization_id = null;

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
        $this->hydrateDestinationOrganizations();
    }

    public function updatedDestinationWoredaId($woredaId)
    {
        $woreda = $woredaId
            ? Woreda::query()->with('zone:id,region_id,country_id')->find($woredaId)
            : null;
        if (! $woreda) {
            $this->destination_organization_id = null;
            $this->hydrateDestinationOrganizations();

            return;
        }

        $this->destination_organization_id = null;
        $this->destination_zone_id = $woreda->zone_id;
        $this->destination_region_id = $woreda->zone?->region_id;
        $this->destination_country_id = $woreda->zone?->country_id;
        $this->hydrateDestinationOptions();
    }

    private function hydrateSourceOptions(): void
    {
        $this->sourceRegions = $this->country_id
            ? $this->applyActorScopeToRegionQuery(Region::query())
            ->where('country_id', $this->country_id)
            ->orderBy('name')
            ->get(['id', 'name'])
            : [];

        $this->sourceZones = $this->region_id
            ? $this->applyActorScopeToZoneQuery(Zone::query())
            ->where('region_id', $this->region_id)
            ->orderBy('name')
            ->get(['id', 'name'])
            : [];

        $this->sourceWoredas = $this->zone_id
            ? $this->applyActorScopeToWoredaQuery(Woreda::query())
            ->where('zone_id', $this->zone_id)
            ->orderBy('name')
            ->get(['id', 'name'])
            : [];

        if ($this->region_id && ! $this->sourceRegions->contains('id', (int) $this->region_id)) {
            $this->region_id = null;
            $this->zone_id = null;
            $this->woreda_id = null;
            $this->organization_id = null;
        }

        if ($this->zone_id && ! $this->sourceZones->contains('id', (int) $this->zone_id)) {
            $this->zone_id = null;
            $this->woreda_id = null;
            $this->organization_id = null;
        }

        if ($this->woreda_id && ! $this->sourceWoredas->contains('id', (int) $this->woreda_id)) {
            $this->woreda_id = null;
            $this->organization_id = null;
        }
    }

    private function hydrateDestinationOptions(): void
    {
        // Destinations are not limited to the actor's scope; only hierarchy consistency applies
        $this->destinationRegions = $this->destination_country_id
            ? Region::query()
            ->where('country_id', $this->destination_country_id)
            ->orderBy('name')
            ->get(['id', 'name'])
            : [];

        $this->destinationZones = $this->destination_region_id
            ? Zone::query()
            ->where('region_id', $this->destination_region_id)
            ->orderBy('name')
            ->get(['id', 'name'])
            : [];

        $this->destinationWoredas = $this->destination_zone_id
            ? Woreda::query()
            ->where('zone_id', $this->destination_zone_id)
            ->orderBy('name')
            ->get(['id', 'name'])
            : [];

        if ($this->destination_region_id && ! $this->destinationRegions->contains('id', (int) $this->destination_region_id)) {
            $this->destination_region_id = null;
            $this->destination_zone_id = null;
            $this->destination_woreda_id = null;
            $this->destination_organization_id = null;
        }

        if ($this->destination_zone_id && ! $this->destinationZones->contains('id', (int) $this->destination_zone_id)) {
            $this->destination_zone_id = null;
            $this->destination_woreda_id = null;
            $this->destination_organization_id = null;
        }

        if ($this->destination_woreda_id && ! $this->destinationWoredas->contains('id', (int) $this->destination_woreda_id)) {
            $this->destination_woreda_id = null;
            $this->destination_organization_id = null;
        }

        $this->hydrateDestinationOrganizations();
    }

    private function hydrateDestinationOrganizations(): void
    {
        // Narrow by the most specific destination location selected
        $query = Organization::query();

        if ($this->destination_woreda_id) {
            $query->where('woreda_id', $this->destination_woreda_id);
        } elseif ($this->destination_zone_id) {
            $query->where('zone_id', $this->destination_zone_id);
        } elseif ($this->destination_region_id) {
            $query->where('region_id', $this->destination_region_id);
        } elseif ($this->destination_country_id) {
            $query->where('country_id', $this->destination_country_id);
        }

        $this->destinationOrganizations = $query->orderBy('name')->get(['id', 'name']);
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
                $book = ManualBook::query()->lockForUpdate()->findOrFail($data['manual_book_id']);

                if (! empty($data['manual_book_package_id'])) {
                    $package = ManualBookPackage::query()->lockForUpdate()->findOrFail($data['manual_book_package_id']);
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

    private function defaultCountryId(): ?int
    {
        $countryId = Country::query()->where('name', 'Ethiopia')->value('id');

        return $countryId !== null ? (int) $countryId : null;
    }

    private function normalizeNullableId($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
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
        // Recompute from the server; the public actor_* properties can be altered by the client
        $this->hydrateActorScope($actor);

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

        // Source must lie inside the actor's access scope; the destination only has to
        // exist and be internally consistent, since books are sent outside one's own area
        $source = $this->alignWithOrganization($source, 'source', $actor);
        $destination = $this->alignWithOrganization($destination, 'destination');

        $this->enforceSourceScope($source, $actor);

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
            $woreda = Woreda::query()->with('zone:id,region_id,country_id')->find($scope['woreda_id']);
            if (! $woreda || ! $woreda->zone) {
                throw new \RuntimeException(ucfirst($label) . ' woreda is invalid.');
            }

            $zone = $woreda->zone;
            $scope['zone_id'] = $scope['zone_id'] ?? $woreda->zone_id;
            $scope['region_id'] = $scope['region_id'] ?? $zone->region_id;
            $scope['country_id'] = $scope['country_id'] ?? $zone->country_id;

            if (! empty($scope['zone_id']) && (int) $scope['zone_id'] !== (int) $woreda->zone_id) {
                throw new \RuntimeException(ucfirst($label) . ' hierarchy mismatch: woreda does not belong to selected zone.');
            }
            if (! empty($scope['region_id']) && (int) $scope['region_id'] !== (int) $zone->region_id) {
                throw new \RuntimeException(ucfirst($label) . ' hierarchy mismatch: woreda does not belong to selected region.');
            }
            if (! empty($scope['country_id']) && (int) $scope['country_id'] !== (int) $zone->country_id) {
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

    private function alignWithOrganization(array $scope, string $label, ?User $restrictTo = null): array
    {
        if (empty($scope['organization_id'])) {
            return $scope;
        }

        $organization = Organization::query()
            ->when($restrictTo, fn ($query) => $query->accessibleBy($restrictTo))
            ->find($scope['organization_id']);
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

    private function enforceSourceScope(array $scope, User $actor): void
    {
        if ($this->actorScopeLevel($actor) === User::ACCESS_LEVEL_NATIONAL) {
            return;
        }

        // Every location the actor is pinned to must be matched exactly; leaving a
        // field empty would widen the source beyond the actor's scope
        $pinned = array_filter([
            'country' => [$this->actor_country_id, $scope['country_id'] ?? null],
            'region' => [$this->actor_region_id, $scope['region_id'] ?? null],
            'zone' => [$this->actor_zone_id, $scope['zone_id'] ?? null],
            'woreda' => [$this->actor_woreda_id, $scope['woreda_id'] ?? null],
            'organization' => [$this->actor_organization_id, $scope['organization_id'] ?? null],
        ], fn (array $pair) => $pair[0] !== null);

        if (empty($pinned)) {
            throw new \RuntimeException('Your account has no assigned location or organization, so it cannot record distributions.');
        }

        foreach ($pinned as $name => [$actorValue, $sourceValue]) {
            if ((int) $sourceValue !== $actorValue) {
                throw new \RuntimeException('Source ' . $name . ' is outside your access scope.');
            }
        }
    }

    private function actorScopeLevel(User $actor): string
    {
        // Mirrors ManualDistribution::scopeAccessibleBy, where Admin sees everything
        if ($actor->hasNationalAccess() || $actor->hasRole('Admin')) {
            return User::ACCESS_LEVEL_NATIONAL;
        }

        return $actor->effectiveAccessLevel();
    }

    private function hydrateActorScope(User $actor): void
    {
        $this->actor_country_id = null;
        $this->actor_region_id = null;
        $this->actor_zone_id = null;
        $this->actor_woreda_id = null;
        $this->actor_organization_id = null;

        $level = $this->actorScopeLevel($actor);

        if ($level === User::ACCESS_LEVEL_NATIONAL) {
            return;
        }

        if ($level === User::ACCESS_LEVEL_ORGANIZATION) {
            $organization = $actor->organization_id
                ? Organization::query()
                ->select('id', 'country_id', 'region_id', 'zone_id', 'woreda_id')
                ->find($actor->organization_id)
                : null;

            // The organization's own location is authoritative for organization-level users
            if ($organization) {
                $this->actor_organization_id = (int) $organization->id;
                $this->actor_country_id = $this->normalizeNullableId($organization->country_id);
                $this->actor_region_id = $this->normalizeNullableId($organization->region_id);
                $this->actor_zone_id = $this->normalizeNullableId($organization->zone_id);
                $this->actor_woreda_id = $this->normalizeNullableId($organization->woreda_id);
            }

            return;
        }

        // Region, zone and woreda users are pinned down to their own level only
        $rank = User::accessLevelRank($level);

        $this->actor_country_id = $this->normalizeNullableId($actor->country_id);
        $this->actor_region_id = $this->normalizeNullableId($actor->region_id);
        $this->actor_zone_id = $rank <= User::accessLevelRank(User::ACCESS_LEVEL_ZONE)
            ? $this->normalizeNullableId($actor->zone_id)
            : null;
        $this->actor_woreda_id = $rank <= User::accessLevelRank(User::ACCESS_LEVEL_WOREDA)
            ? $this->normalizeNullableId($actor->woreda_id)
            : null;
    }

    private function applyActorScopeToRegionQuery($query)
    {
        if ($this->actor_country_id) {
            $query->where('country_id', $this->actor_country_id);
        }

        if ($this->actor_region_id) {
            $query->where('id', $this->actor_region_id);
        }

        return $query;
    }

    private function applyActorScopeToZoneQuery($query)
    {
        if ($this->actor_country_id) {
            $query->where('country_id', $this->actor_country_id);
        }

        if ($this->actor_region_id) {
            $query->where('region_id', $this->actor_region_id);
        }

        if ($this->actor_zone_id) {
            $query->where('id', $this->actor_zone_id);
        }

        return $query;
    }

    private function applyActorScopeToWoredaQuery($query)
    {
        if ($this->actor_country_id) {
            $query->whereHas('zone', function ($zoneQuery): void {
                $zoneQuery->where('country_id', $this->actor_country_id);
            });
        }

        if ($this->actor_region_id) {
            $query->whereHas('zone', function ($zoneQuery): void {
                $zoneQuery->where('region_id', $this->actor_region_id);
            });
        }

        if ($this->actor_zone_id) {
            $query->where('zone_id', $this->actor_zone_id);
        }

        if ($this->actor_woreda_id) {
            $query->where('id', $this->actor_woreda_id);
        }

        return $query;
    }

    private function currentActor(): User
    {
        $actor = Auth::user();

        if ($actor instanceof User) {
            return $actor;
        }

        abort(403);
    }

    private function resetForm(): void
    {
        $this->manual_book_id = null;
        $this->manual_book_package_id = null;
        $this->quantity = null;
        $this->remarks = null;
        $this->organization_id = $this->actor_organization_id;
        $this->country_id = $this->actor_country_id ?? $this->defaultCountryId();
        $this->region_id = $this->actor_region_id;
        $this->zone_id = $this->actor_zone_id;
        $this->woreda_id = $this->actor_woreda_id;
        $this->destination_organization_id = null;
        $this->destination_country_id = $this->actor_country_id ?? $this->defaultCountryId();
        $this->destination_region_id = $this->actor_region_id;
        $this->destination_zone_id = $this->actor_zone_id;
        $this->destination_woreda_id = $this->actor_woreda_id;
        $this->packages = [];
        $this->sourceRegions = [];
        $this->sourceZones = [];
        $this->sourceWoredas = [];
        $this->destinationRegions = [];
        $this->destinationZones = [];
        $this->destinationWoredas = [];

        $this->hydrateSourceOptions();
        $this->hydrateDestinationOptions();
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
