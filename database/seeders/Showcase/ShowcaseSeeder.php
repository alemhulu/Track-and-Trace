<?php

namespace Database\Seeders\Showcase;

use App\Models\Book;
use App\Models\Country;
use App\Models\delivery as Delivery;
use App\Models\Distribution;
use App\Models\DistributionRoute;
use App\Models\DistributionStep;
use App\Models\Grade;
use App\Models\ManualTracking\ManualBook;
use App\Models\ManualTracking\ManualDistribution;
use App\Models\ManualTracking\ManualScan;
use App\Models\ManualTracking\ManualStockBalance;
use App\Models\Organization;
use App\Models\OrganizationType;
use App\Models\Ownership;
use App\Models\Package;
use App\Models\PrintOrder;
use App\Models\Region;
use App\Models\School\SchoolEnrolment;
use App\Models\School\Student;
use App\Models\School\StudentBookIssue;
use App\Models\Sector;
use App\Models\Subject;
use App\Models\User;
use App\Models\WareHouse;
use App\Models\Woreda;
use App\Models\Zone;
use App\Services\ManualTracking\ManualScanService;
use App\Services\Oversight\VerificationService;
use App\Services\ManualTracking\ManualShipmentService;
use App\Services\School\SchoolBookService;
use App\Support\AcademicYear;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Milon\Barcode\DNS1D;
use Milon\Barcode\DNS2D;

/**
 * Seeds one connected, realistic academic year of textbook logistics:
 * print orders -> printer warehouse -> MoE -> regional bureau -> zone -> woreda -> school,
 * plus routes, distribution plans, student hand-outs and manual tracking.
 *
 * Quantities are conserved on every leg: a node receives exactly what its parent sent it,
 * and balance = received - sent. Dates are relative to now() so dashboards show activity.
 *
 * Expects the reference seeders (permissions, locations, grades, subjects, types) to have run.
 */
class ShowcaseSeeder extends Seeder
{
    private const LEVELS = ['printer', 'moe', 'region', 'zone', 'woreda', 'school'];

    private const STAGES = ['requested', 'accepted', 'printed', 'moe', 'region', 'zone', 'woreda', 'school'];

    private const PACKAGE_REQUESTED = 0;
    private const PACKAGE_ACCEPTED = 1;
    private const PACKAGE_STORED = 2;
    private const PACKAGE_SENDING = 3;
    private const PACKAGE_SENT = 4;

    // Packages per print order whose QR and barcode SVGs are written to storage
    private const IMAGE_PACKAGES_PER_ORDER = 2;

    private Carbon $now;
    private Carbon $setupDate;
    private int $ecYear;
    private string $yearLabel;
    private string $passwordHash;
    private int $personIndex = 0;
    private int $orderSequence = 0;

    /** @var array<string, array> */
    private array $nodes = [];

    /** @var array<string, string> kind => node key */
    private array $printerKeys = [];

    /** @var array<string, DistributionRoute> "fromKey>toKey" => route */
    private array $routes = [];

    /** @var array<int, array{order: PrintOrder, codes: array}> */
    private array $imageQueue = [];

    private ?User $moeOfficer = null;

    private array $lookupCache = [];

    private ?ManualScanService $scanService = null;

    private int $problemShipments = 0;

    public function run(): void
    {
        if (Organization::query()->where('name', ShowcaseData::MINISTRY['name'])->exists()) {
            $this->command?->warn('Showcase data is already present. Rebuild with: SEED_PROFILE=showcase php artisan migrate:fresh --seed');

            return;
        }

        mt_srand(2019);

        $this->now = now()->startOfHour();
        $this->setupDate = $this->now->copy()->subDays(160);
        $this->ecYear = AcademicYear::current($this->now);
        $this->yearLabel = AcademicYear::label($this->ecYear);
        $this->passwordHash = Hash::make(ShowcaseData::PASSWORD);

        DB::transaction(function (): void {
            $this->seedOrganizations();
            $this->seedUsers();
            $books = $this->seedBooks();
            $this->seedRoutes();
            $progressByBook = $this->seedPrintOrdersAndPackages($books);
            $this->seedPendingOrders($books);
            $this->seedDistributionPlans($books, $progressByBook);
            $this->seedStudentDeliveries($books, $progressByBook);
            $this->seedManualTracking();
            $this->seedSchoolRecords();
        });

        $this->writeCodeImages();
        $this->printSummary();
    }

    // ---------------------------------------------------------------------
    // Organizations, warehouses and users
    // ---------------------------------------------------------------------

    private function seedOrganizations(): void
    {
        $country = Country::query()->firstOrCreate(['name' => 'Ethiopia'], ['code' => 'ET']);
        $addis = Region::query()->updateOrCreate(
            ['name' => 'Addis Ababa', 'country_id' => $country->id],
            ['code' => 'AA', 'is_city' => true]
        );
        $branch = ['region' => 1200, 'zone' => 1300, 'woreda' => 1400, 'school' => 2000];

        $this->addNode('moe', 'moe', ShowcaseData::MINISTRY['name'], 'Ministry', [
            'country' => $country, 'region' => $addis,
        ], [
            'branch' => ShowcaseData::MINISTRY['branch'],
            'year' => ShowcaseData::MINISTRY['year'],
            'gps' => [9.0300, 38.7600],
            'area' => '011',
            'sector' => 'ESC',
            'code' => 'MOE',
            'short' => 'MoE Central Warehouse',
            'facilities' => ['Central warehouse', 'Loading bay', 'Fleet yard'],
        ]);

        foreach (ShowcaseData::PRINTERS as $kind => $printer) {
            $key = 'printer:' . $kind;
            $this->printerKeys[$kind] = $key;
            $this->addNode($key, 'printer', $printer['name'], 'Printer', [
                'country' => $country, 'region' => $addis,
            ], [
                'branch' => $printer['branch'],
                'year' => $printer['year'],
                'gps' => $kind === 'primary' ? [8.9800, 38.7700] : [9.0400, 38.7500],
                'area' => '011',
                'sector' => 'LGD',
                'code' => 'PRN-' . strtoupper(Str::substr(Str::slug($printer['short'], ''), 0, 6)),
                'short' => $printer['short'],
                'facilities' => ['Offset press', 'Bindery', 'Finished goods warehouse'],
            ]);
        }

        foreach (ShowcaseData::LOGISTICS_HUBS as $hubKey => $hub) {
            $hubCountry = Country::query()->firstOrCreate(['name' => $hub['country']['name']], ['code' => $hub['country']['code']]);
            $this->addNode('hub:' . $hubKey, 'hub', $hub['name'], 'Logistics Hub', [
                'country' => $hubCountry,
                'region' => $hub['region'] ? Region::query()->where('name', $hub['region'])->first() : null,
            ], [
                'branch' => $hub['branch'],
                'year' => 2010,
                'gps' => $hub['gps'],
                'area' => '011',
                'sector' => 'LGD',
                'code' => 'HUB-' . strtoupper($hubKey),
                'facilities' => ['Bonded warehouse', 'Loading bay'],
            ]);
        }

        foreach (ShowcaseData::GEOGRAPHY as $regionName => $regionData) {
            $region = Region::query()->updateOrCreate(
                ['name' => $regionName, 'country_id' => $country->id],
                ['code' => $regionData['code'], 'is_city' => $regionData['is_city']]
            );
            $firstWoreda = collect($regionData['zones'])->flatMap(fn ($zone) => $zone['woredas'])->first();
            $regionKey = 'region:' . $regionName;

            $this->addNode($regionKey, 'region', $regionData['bureau'], 'Regional Bureau', [
                'country' => $country, 'region' => $region,
            ], [
                'branch' => ++$branch['region'],
                'year' => 1995,
                'gps' => $firstWoreda['gps'],
                'area' => $firstWoreda['area'],
                'sector' => 'ESC',
                'code' => 'REB-' . $regionData['code'],
                'facilities' => ['Regional warehouse', 'Loading bay'],
            ], 'moe');

            foreach ($regionData['zones'] as $zoneName => $zoneData) {
                $zone = Zone::query()->updateOrCreate(
                    ['name' => $zoneName, 'region_id' => $region->id, 'country_id' => $country->id],
                    ['code' => $zoneData['code'], 'is_subcity' => $zoneData['is_subcity']]
                );
                $zoneFirstWoreda = collect($zoneData['woredas'])->first();
                $zoneKey = 'zone:' . $zoneName;

                $this->addNode($zoneKey, 'zone', $zoneData['office'], 'Zone Bureau', [
                    'country' => $country, 'region' => $region, 'zone' => $zone,
                ], [
                    'branch' => ++$branch['zone'],
                    'year' => 1998,
                    'gps' => $zoneFirstWoreda['gps'],
                    'area' => $zoneFirstWoreda['area'],
                    'sector' => 'LGD',
                    'code' => $regionData['code'] . '-' . $zoneData['code'],
                    'facilities' => ['Zone store', 'Loading bay'],
                ], $regionKey);

                foreach ($zoneData['woredas'] as $woredaName => $woredaData) {
                    $woreda = Woreda::query()->updateOrCreate(
                        ['name' => $woredaName, 'zone_id' => $zone->id],
                        ['code' => $woredaData['code'], 'country_id' => $country->id, 'region_id' => $region->id]
                    );
                    $woredaKey = 'woreda:' . $woredaName;
                    $location = ['country' => $country, 'region' => $region, 'zone' => $zone, 'woreda' => $woreda];
                    $codePrefix = $regionData['code'] . '-' . $zoneData['code'] . '-' . $woredaData['code'];

                    $this->addNode($woredaKey, 'woreda', $woredaName . ' Education Office', 'Woreda Bureau', $location, [
                        'branch' => ++$branch['woreda'],
                        'year' => 2001,
                        'gps' => $woredaData['gps'],
                        'area' => $woredaData['area'],
                        'sector' => 'LGD',
                        'code' => $codePrefix,
                        'facilities' => ['Woreda store'],
                    ], $zoneKey);

                    foreach (['primary' => 'PS', 'secondary' => 'SS'] as $kind => $suffix) {
                        $this->addNode('school:' . $woredaName . ':' . $kind, 'school', $woredaData[$kind], 'School', $location, [
                            'branch' => ++$branch['school'],
                            'year' => mt_rand(1958, 2012),
                            'gps' => $this->jitter($woredaData['gps']),
                            'area' => $woredaData['area'],
                            'sector' => 'ESC',
                            'code' => $codePrefix . '-' . $suffix,
                            'kind' => $kind,
                            'facilities' => $this->pick(['Library', 'Book store room', 'Pedagogical center', 'ICT lab', 'Science lab'], 3),
                        ], $woredaKey);
                    }
                }
            }
        }
    }

    private function addNode(string $key, string $level, string $name, string $type, array $location, array $meta, ?string $parent = null): void
    {
        $gps = $meta['gps'];
        $organization = $this->persist(new Organization(), [
            'name' => $name,
            'email' => Str::slug($name, '.') . '@' . ShowcaseData::EMAIL_DOMAIN,
            'telephone' => $this->landline($meta['area']),
            'x-coordinate' => $gps[1],
            'y-coordinate' => $gps[0],
            'gps' => sprintf('%.4f, %.4f', $gps[0], $gps[1]),
            'year' => $meta['year'],
            'old_code' => 'ORG-' . str_pad((string) $meta['branch'], 5, '0', STR_PAD_LEFT),
            'new_code' => 'ET-' . $meta['code'],
            'facilities' => json_encode($meta['facilities']),
            'location' => true,
            'status' => true,
            'organization_type_id' => $this->lookup(OrganizationType::class, 'name', $type),
            'country_id' => $location['country']->id,
            'region_id' => $location['region']->id ?? null,
            'zone_id' => isset($location['zone']) ? $location['zone']->id : null,
            'woreda_id' => isset($location['woreda']) ? $location['woreda']->id : null,
            'sector_id' => $this->lookup(Sector::class, 'code', $meta['sector']),
            'ownership_id' => $this->lookup(Ownership::class, 'name', 'Public'),
            'created_at' => $this->setupDate,
            'updated_at' => $this->setupDate,
        ]);

        $warehouse = $this->persist(new WareHouse(), [
            'branch' => $meta['branch'],
            'organization_id' => $organization->id,
            'country_id' => $organization->country_id,
            'region_id' => $organization->region_id,
            'zone_id' => $organization->zone_id,
            'woreda_id' => $organization->woreda_id,
            'created_at' => $this->setupDate,
            'updated_at' => $this->setupDate,
        ]);

        $this->nodes[$key] = [
            'key' => $key,
            'level' => $level,
            'org' => $organization,
            'warehouse' => $warehouse,
            'location' => $location,
            'short' => $meta['short'] ?? $name,
            'code' => $meta['code'],
            'kind' => $meta['kind'] ?? null,
            // Relative school size: drives enrolment and therefore every quantity upstream
            'factor' => $level === 'school' ? mt_rand(65, 145) / 100 : 1.0,
            'parent' => $parent,
            'children' => [],
            'user' => null,
        ];

        if ($parent) {
            $this->nodes[$parent]['children'][] = $key;
        }
    }

    private function seedUsers(): void
    {
        $this->moeOfficer = $this->makeUser('moe.officer', 'National Admin', User::ACCESS_LEVEL_NATIONAL, 'Textbook Distribution Coordinator', []);
        $this->assignUser('moe', $this->moeOfficer);

        foreach ($this->nodes as $key => $node) {
            $location = $node['location'];

            $user = match ($node['level']) {
                'printer' => $this->makeUser(Str::slug($node['short'], '') . '.store', 'Organization User', User::ACCESS_LEVEL_ORGANIZATION, 'Finished Goods Store Manager', $location, $node['org']),
                'hub' => $this->makeUser(ShowcaseData::LOGISTICS_HUBS[Str::after($key, 'hub:')]['email'], 'Organization User', User::ACCESS_LEVEL_ORGANIZATION, 'Logistics Officer', $location, $node['org']),
                'region' => $this->makeUser(Str::slug($location['region']->name, '') . '.bureau', 'Region Officer', User::ACCESS_LEVEL_REGION, 'Regional Textbook Officer', [
                    'country' => $location['country'], 'region' => $location['region'],
                ]),
                'zone' => $this->makeUser(Str::slug($location['zone']->name, '.'), 'Zone Officer', User::ACCESS_LEVEL_ZONE, 'Zone Supply Officer', [
                    'country' => $location['country'], 'region' => $location['region'], 'zone' => $location['zone'],
                ]),
                'woreda' => $this->makeUser(Str::slug($location['woreda']->name, '.'), 'Woreda Officer', User::ACCESS_LEVEL_WOREDA, 'Woreda Education Officer', $location),
                'school' => $this->makeUser('director.' . Str::slug(Str::replaceLast(' School', '', $node['org']->name), '.'), 'School User', User::ACCESS_LEVEL_ORGANIZATION, 'School Director', $location, $node['org']),
                default => null,
            };

            if ($user) {
                $this->assignUser($key, $user);
            }
        }
    }

    private function makeUser(string $emailLocal, string $role, string $level, string $position, array $location, ?Organization $organization = null): User
    {
        $user = $this->persist(new User(), [
            'name' => $this->personName(),
            'email' => $emailLocal . '@' . ShowcaseData::EMAIL_DOMAIN,
            'password' => $this->passwordHash,
            'email_verified_at' => $this->setupDate,
            'phone' => $this->mobile(),
            'position' => $position,
            'access_level' => $level,
            'organization_id' => $organization?->id,
            'country_id' => isset($location['country']) ? $location['country']->id : null,
            'region_id' => isset($location['region']) ? $location['region']->id : null,
            'zone_id' => isset($location['zone']) ? $location['zone']->id : null,
            'woreda_id' => isset($location['woreda']) ? $location['woreda']->id : null,
            'created_at' => $this->setupDate,
            'updated_at' => $this->setupDate,
        ]);

        $user->assignRole($role);

        return $user;
    }

    private function assignUser(string $key, User $user): void
    {
        $this->nodes[$key]['user'] = $user;
        $this->nodes[$key]['org']->forceFill([
            'assigned_user_id' => $user->id,
            'manager_name' => $user->name,
            'phone' => $user->phone,
            'user_id' => $this->moeOfficer?->id,
        ])->save();
        $this->nodes[$key]['warehouse']->forceFill(['assigned_user_id' => $user->id])->save();
    }

    // ---------------------------------------------------------------------
    // Books, routes, print orders and packages
    // ---------------------------------------------------------------------

    /**
     * @return array<int, array{book: Book, grade: string, subject: string, kind: string, title: string}>
     */
    private function seedBooks(): array
    {
        $grades = Grade::query()->pluck('id', 'name');
        $subjects = Subject::query()->pluck('id', 'name');
        $books = [];
        $title = 0;

        foreach (ShowcaseData::CURRICULUM as $grade => $subjectNames) {
            foreach ($subjectNames as $subjectName) {
                if (! isset($grades[$grade], $subjects[$subjectName])) {
                    throw new \RuntimeException("Grade {$grade} or subject {$subjectName} is missing; run the reference seeders first.");
                }

                $kind = (int) $grade <= 8 ? 'primary' : 'secondary';
                $book = $this->persist(new Book(), [
                    'grade_id' => $grades[$grade],
                    'subject_id' => $subjects[$subjectName],
                    'isbn' => $this->isbn($kind === 'primary' ? 0 : 1, ++$title),
                    'volume' => 1,
                    'edition' => (int) $grade >= 9 ? 2 : 1,
                    'book_type' => false,
                    'print_type' => 'Offset',
                    'paper_size' => 'A4',
                    'file_location' => '/books/grade-' . $grade . '-' . Str::slug($subjectName) . '.pdf',
                    'created_at' => $this->setupDate,
                    'updated_at' => $this->setupDate,
                ]);

                $books[$book->id] = [
                    'book' => $book,
                    'grade' => (string) $grade,
                    'subject' => $subjectName,
                    'kind' => $kind,
                    'title' => "Grade {$grade} {$subjectName}",
                ];
            }
        }

        return $books;
    }

    private function seedRoutes(): void
    {
        $planned = $this->now->copy()->subDays(150);
        $legNumber = ['moe' => 1, 'region' => 2, 'zone' => 3, 'woreda' => 4, 'school' => 5];
        $edges = [];

        foreach ($this->printerKeys as $printerKey) {
            $edges[] = [$printerKey, 'moe'];
        }

        foreach ($this->nodes as $key => $node) {
            foreach ($node['children'] as $childKey) {
                $edges[] = [$key, $childKey];
            }
        }

        foreach ($edges as [$fromKey, $toKey]) {
            $from = $this->nodes[$fromKey];
            $to = $this->nodes[$toKey];

            $this->routes[$fromKey . '>' . $toKey] = $this->persist(new DistributionRoute(), [
                'name' => $from['short'] . ' → ' . $to['short'],
                'description' => sprintf(
                    'Leg %d of the national textbook chain: %s (branch %d) to %s (branch %d).',
                    $legNumber[$to['level']],
                    $from['org']->name,
                    $from['warehouse']->branch,
                    $to['org']->name,
                    $to['warehouse']->branch
                ),
                'from_ware_house_id' => $from['warehouse']->id,
                'to_ware_house_id' => $to['warehouse']->id,
                'is_active' => true,
                'created_at' => $planned,
                'updated_at' => $planned,
            ]);
        }
    }

    /**
     * @return array<int, array{stage: string, transit: bool, legs: int}> keyed by book id
     */
    private function seedPrintOrdersAndPackages(array $books): array
    {
        $progressByBook = [];

        foreach ($books as $bookId => $entry) {
            $progress = ShowcaseData::BOOK_PROGRESS_OVERRIDES[$entry['grade'] . '|' . $entry['subject']]
                ?? ShowcaseData::GRADE_PROGRESS[$entry['grade']];

            $quantities = $this->allocate($entry);
            $timeline = $this->timeline($progress['stage'], $progress['transit']);
            $printed = $quantities['moe'];

            $order = $this->createPrintOrder($entry, $printed, $progress['stage'], $timeline);
            $reached = array_search($this->stageLevel($progress['stage']), self::LEVELS, true);

            if ($reached !== false) {
                $this->seedPackageTree($order, $entry, $quantities, $timeline, (int) $reached, $progress['transit']);
            }

            $progressByBook[$bookId] = $progress + ['legs' => $reached === false ? 0 : (int) $reached];
        }

        return $progressByBook;
    }

    /**
     * Bottom-up demand: schools need one copy per student, each level above keeps a small
     * reserve and rounds up to whole packages.
     *
     * @return array<string, int> node key => quantity received (plus 'moe' = print run)
     */
    private function allocate(array $entry, string $root = 'moe'): array
    {
        $quantities = [];
        $reserve = ['woreda' => 1.02, 'zone' => 1.01, 'region' => 1.01, 'moe' => 1.02];

        $walk = function (string $key) use (&$walk, &$quantities, $entry, $reserve): int {
            $node = $this->nodes[$key];

            if ($node['level'] === 'school') {
                if ($node['kind'] !== $entry['kind']) {
                    return 0;
                }

                return $quantities[$key] = (int) round(ShowcaseData::ENROLMENT[$entry['grade']] * $node['factor']);
            }

            $childTotal = 0;
            foreach ($node['children'] as $childKey) {
                $childTotal += $walk($childKey);
            }

            return $quantities[$key] = $this->roundUpToPackage((int) ceil($childTotal * $reserve[$node['level']]));
        };

        $walk($root);

        return $quantities;
    }

    /**
     * Day offsets for every event of one print order, shifted so the latest past event
     * happened shortly before now and an in-transit leg arrives after now.
     *
     * @return array<string, Carbon|null>
     */
    private function timeline(string $stage, bool $transit): array
    {
        $durations = [
            'accepted' => mt_rand(2, 4),
            'printed' => mt_rand(18, 26),
            'moe' => mt_rand(2, 4),
            'region' => mt_rand(5, 9),
            'zone' => mt_rand(4, 7),
            'woreda' => mt_rand(3, 6),
            'school' => mt_rand(2, 4),
        ];

        $offsets = ['requested' => 0];
        $cursor = 0;
        foreach ($durations as $event => $days) {
            $cursor += $days;
            $offsets[$event] = $cursor;
        }

        $stageIndex = array_search($stage, self::STAGES, true);
        $lastPast = $offsets[$stage];
        $next = self::STAGES[$stageIndex + 1] ?? null;

        if ($transit && $next) {
            // Dispatched 1-2 days after arrival; arrives after now
            $dispatch = $lastPast + mt_rand(1, 2);
            $offsets['dispatch'] = $dispatch;
            $offsets['eta'] = $dispatch + $durations[$next];
            $lastPast = $dispatch;
            $slack = mt_rand(0, max(0, $durations[$next] - 2));
        } else {
            // Completed chains are spread further back so trend charts have history
            $slack = $stage === 'school' ? mt_rand(4, 40) : mt_rand(1, 6);
        }

        $start = $this->now->copy()->subDays($lastPast + $slack)->setTime(mt_rand(8, 15), mt_rand(0, 59));
        $timeline = [];

        foreach ($offsets as $event => $offset) {
            $reachable = in_array($event, ['dispatch', 'eta'], true)
                || array_search($event, self::STAGES, true) <= $stageIndex;
            $timeline[$event] = $reachable ? $start->copy()->addDays($offset) : null;
        }

        $timeline['expected_print'] = $start->copy()->addDays(21);

        return $timeline;
    }

    private function createPrintOrder(array $entry, int $copies, string $stage, array $timeline, ?string $note = null, ?int $status = null): PrintOrder
    {
        $sequence = ++$this->orderSequence;
        $packages = (int) ceil($copies / ShowcaseData::BOOKS_PER_PACKAGE);
        $barcodeStart = $this->barcode($sequence, 0);
        $qrcodeStart = $this->qrcode($sequence, 0);
        $codes = $this->bookCodes($barcodeStart, $qrcodeStart, $copies);
        $stageIndex = array_search($stage, self::STAGES, true);
        $isPrinted = $stageIndex >= array_search('printed', self::STAGES, true);
        $requestStatus = $status ?? match (true) {
            $stage === 'requested' => 0,
            $stage === 'accepted' => 1,
            $stage === 'printed' => 2,
            default => 3,
        };
        $lastUpdate = $timeline['dispatch'] ?? $timeline[$stage] ?? $timeline['requested'];

        $order = $this->persist(new PrintOrder(), [
            'order_organization_id' => $this->nodes['moe']['org']->id,
            'printer_organization_id' => $this->nodes[$this->printerKeys[$entry['kind']]]['org']->id,
            'book_id' => $entry['book']->id,
            'no_of_books' => $copies,
            'no_of_packages' => $packages,
            'book_per_package' => ShowcaseData::BOOKS_PER_PACKAGE,
            'Book_codes' => $codes,
            'barcode_start' => $barcodeStart,
            'barcode_end' => $barcodeStart + $copies - 1,
            'qrcode_start' => $qrcodeStart,
            'qrcode_end' => $qrcodeStart + $packages - 1,
            'expected_print_time' => $timeline['expected_print']->toDateString(),
            'actual_print_time' => $isPrinted ? $timeline['printed']->toDateString() : null,
            'print_status' => $isPrinted ? 1 : 0,
            'request_status' => $requestStatus,
            'description' => $note ?? sprintf('%s student textbook, academic year %s.', $entry['title'], $this->yearLabel),
            'created_at' => $timeline['requested'],
            'updated_at' => $lastUpdate,
        ]);

        $this->imageQueue[] = ['order' => $order, 'codes' => $codes, 'title' => $entry['title']];

        return $order;
    }

    /**
     * One package row per warehouse a print order passes through. Each child gets a
     * contiguous slice of its parent's barcode range, so any copy can be traced back.
     */
    private function seedPackageTree(PrintOrder $order, array $entry, array $quantities, array $timeline, int $reached, bool $transit): void
    {
        $printerKey = $this->printerKeys[$entry['kind']];
        $copies = $quantities['moe'];

        $this->createPackageRow($order, $entry, $printerKey, $printerKey, $copies, 0, $reached, $transit, $timeline, 0, ['moe' => $copies]);

        $walk = function (string $key, string $senderKey, int $rangeOffset) use (&$walk, $order, $entry, $quantities, $timeline, $reached, $transit): void {
            $level = array_search($this->nodes[$key]['level'], self::LEVELS, true);
            $isTransitTarget = $transit && $level === $reached + 1;

            if ($level > $reached && ! $isTransitTarget) {
                return;
            }

            $children = array_filter(
                $this->nodes[$key]['children'],
                fn ($childKey) => ($quantities[$childKey] ?? 0) > 0
            );
            $childQuantities = array_intersect_key($quantities, array_flip($children));

            $this->createPackageRow($order, $entry, $key, $senderKey, $quantities[$key], $level, $reached, $transit, $timeline, $rangeOffset, $childQuantities);

            $childOffset = $rangeOffset;
            foreach ($children as $childKey) {
                $walk($childKey, $key, $childOffset);
                $childOffset += $quantities[$childKey];
            }
        };

        $walk('moe', $printerKey, 0);
    }

    private function createPackageRow(
        PrintOrder $order,
        array $entry,
        string $key,
        string $senderKey,
        int $quantity,
        int $level,
        int $reached,
        bool $transit,
        array $timeline,
        int $rangeOffset,
        array $childQuantities
    ): void {
        $node = $this->nodes[$key];
        $levelName = self::LEVELS[$level];
        $arrivalEvent = $levelName === 'printer' ? 'printed' : $levelName;
        $inTransit = $level === $reached + 1;
        $holding = $level === $reached;
        $forwards = $level < $reached || ($holding && $transit && $levelName !== 'school');

        $received = $inTransit ? 0 : $quantity;
        $sent = $forwards ? array_sum($childQuantities) : 0;
        $arrivedAt = $inTransit ? null : $timeline[$arrivalEvent];
        $nextLevel = self::LEVELS[$level + 1] ?? null;
        $dispatchedAt = match (true) {
            ! $forwards => null,
            $holding => $timeline['dispatch'],
            default => $timeline[$nextLevel]->copy()->subDays(mt_rand(1, 2)),
        };

        $status = match (true) {
            $inTransit => self::PACKAGE_ACCEPTED,
            $holding && $forwards => self::PACKAGE_SENDING,
            $forwards => self::PACKAGE_SENT,
            default => self::PACKAGE_STORED,
        };

        $sender = $this->nodes[$senderKey]['org'];
        $packagesHeld = (int) ceil($quantity / ShowcaseData::BOOKS_PER_PACKAGE);
        $createdAt = $arrivedAt ?? $timeline['dispatch'];

        $this->persist(new Package(), [
            'ware_house_id' => $node['warehouse']->id,
            'print_order_id' => $order->id,
            'sender_organization_id' => $sender->id,
            'receiver_organization_id' => $node['org']->id,
            'step' => $level,
            'Book_codes' => $levelName === 'printer' ? $order->Book_codes : null,
            'no_of_books' => $quantity,
            'received' => $received,
            'sent' => $sent,
            'balance' => $received - $sent,
            'books_per_package' => ShowcaseData::BOOKS_PER_PACKAGE,
            'barcode_start' => $order->barcode_start + $rangeOffset,
            'barcode_end' => $order->barcode_start + $rangeOffset + $quantity - 1,
            'qrcode_start' => $order->qrcode_start + intdiv($rangeOffset, ShowcaseData::BOOKS_PER_PACKAGE),
            'qrcode_end' => $order->qrcode_start + intdiv($rangeOffset + $quantity - 1, ShowcaseData::BOOKS_PER_PACKAGE),
            'expected_send_time' => $dispatchedAt?->toDateString()
                ?? ($levelName !== 'school' && ! $inTransit ? $this->now->copy()->addDays(mt_rand(3, 12))->toDateString() : null),
            'actual_send_time' => $dispatchedAt?->toDateString(),
            'expected_delivery_school_time' => $inTransit
                ? $timeline['eta']->toDateString()
                : $arrivedAt->copy()->subDays(mt_rand(0, 2))->toDateString(),
            'actual_delivery_school_time' => $arrivedAt?->toDateString(),
            'request_status' => $status,
            'delivery_status' => ! $inTransit,
            'description' => match (true) {
                $levelName === 'printer' => sprintf('%s: %d copies in %d packages off the press.', $entry['title'], $quantity, $packagesHeld),
                $inTransit => sprintf('%s: %d copies in transit from %s.', $entry['title'], $quantity, $sender->name),
                default => sprintf('%s: %d copies received from %s.', $entry['title'], $quantity, $sender->name),
            },
            'subject_id' => $entry['book']->subject_id,
            'grade_id' => $entry['book']->grade_id,
            'created_at' => $createdAt,
            'updated_at' => $dispatchedAt ?? $createdAt,
        ]);
    }

    private function seedPendingOrders(array $books): void
    {
        foreach (ShowcaseData::PENDING_ORDERS as $pending) {
            $entry = collect($books)->first(
                fn ($book) => $book['grade'] === $pending['grade'] && $book['subject'] === $pending['subject']
            );

            if (! $entry) {
                continue;
            }

            $requestedAt = $this->now->copy()->subDays($pending['days_ago'])->setTime(10, 30);
            $stage = $pending['status'] === 1 ? 'accepted' : 'requested';

            $this->createPrintOrder($entry, $pending['copies'], $stage, [
                'requested' => $requestedAt,
                'accepted' => $pending['status'] === 1 ? $requestedAt->copy()->addDays(3) : null,
                'expected_print' => $requestedAt->copy()->addDays(30),
            ], $pending['note'], $pending['status']);
        }
    }

    // ---------------------------------------------------------------------
    // Distribution plans and student hand-outs
    // ---------------------------------------------------------------------

    private function seedDistributionPlans(array $books, array $progressByBook): void
    {
        $legsByKind = [];
        foreach ($progressByBook as $bookId => $progress) {
            // Books still at the printer do not hold a plan back
            if ($progress['legs'] > 0) {
                $legsByKind[$books[$bookId]['kind']][] = $progress['legs'];
            }
        }

        foreach ($this->nodes as $key => $node) {
            if ($node['level'] !== 'school') {
                continue;
            }

            $woredaKey = $node['parent'];
            $zoneKey = $this->nodes[$woredaKey]['parent'];
            $regionKey = $this->nodes[$zoneKey]['parent'];
            $printerKey = $this->printerKeys[$node['kind']];
            $path = [[$printerKey, 'moe'], ['moe', $regionKey], [$regionKey, $zoneKey], [$zoneKey, $woredaKey], [$woredaKey, $key]];
            $grades = $node['kind'] === 'primary' ? 'grades 1-8' : 'grades 9-12';
            $createdAt = $this->now->copy()->subDays(140);

            $distribution = $this->persist(new Distribution(), [
                'name' => sprintf('%s Textbooks - %s', $this->yearLabel, $node['org']->name),
                'description' => sprintf(
                    'Annual allocation of %s student textbooks for %s, %s, via %s.',
                    $grades,
                    $node['org']->name,
                    $node['location']['woreda']->name,
                    $this->nodes[$zoneKey]['org']->name
                ),
                'is_active' => true,
                'printer_id' => $this->nodes[$printerKey]['org']->id,
                'moe_id' => $this->nodes['moe']['org']->id,
                'region_id' => $node['location']['region']->id,
                'zone_id' => $node['location']['zone']->id,
                'woreda_id' => $node['location']['woreda']->id,
                'school_id' => $node['org']->id,
                'step' => min($legsByKind[$node['kind']] ?? [0]),
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            foreach ($path as $index => [$fromKey, $toKey]) {
                $this->persist(new DistributionStep(), [
                    'distribution_id' => $distribution->id,
                    'route_id' => $this->routes[$fromKey . '>' . $toKey]->id,
                    'step_order' => $index + 1,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);
            }
        }
    }

    private function seedStudentDeliveries(array $books, array $progressByBook): void
    {
        $booksByGrade = [];
        foreach ($books as $bookId => $entry) {
            if ($progressByBook[$bookId]['stage'] === 'school') {
                $booksByGrade[$entry['grade']][] = $entry;
            }
        }

        foreach ($this->nodes as $node) {
            if ($node['level'] !== 'school') {
                continue;
            }

            $grades = array_values(array_filter(
                array_keys($booksByGrade),
                fn ($grade) => ((int) $grade <= 8) === ($node['kind'] === 'primary')
            ));
            $sequence = 0;

            foreach (array_slice($grades, 0, 3) as $grade) {
                $handedOutAt = Package::query()
                    ->where('receiver_organization_id', $node['org']->id)
                    ->where('grade_id', $booksByGrade[$grade][0]['book']->grade_id)
                    ->value('created_at');
                $handedOutAt = Carbon::parse($handedOutAt ?? $this->now->copy()->subDays(10));

                for ($student = 0; $student < 5; $student++) {
                    $date = $handedOutAt->copy()->addDays(mt_rand(1, 5))->min($this->now);

                    $this->persist(new Delivery(), [
                        'student_id' => sprintf('%s-G%02d-%03d', $node['code'], (int) $grade, ++$sequence),
                        'name' => $this->personName(),
                        'books' => json_encode(array_map(fn ($entry) => [
                            'book_id' => $entry['book']->id,
                            'isbn' => $entry['book']->isbn,
                            'title' => $entry['title'],
                            'quantity' => 1,
                        ], $booksByGrade[$grade])),
                        'distributed' => mt_rand(1, 10) <= 9 ? 1 : 0,
                        'created_at' => $date,
                        'updated_at' => $date,
                    ]);
                }
            }
        }
    }

    // ---------------------------------------------------------------------
    // Manual tracking
    // ---------------------------------------------------------------------

    private function seedManualTracking(): void
    {
        $titlesByRegion = $this->seedManualTitles();

        try {
            foreach (ShowcaseData::MANUAL_ROUTING as $regionName => $routing) {
                $this->shipManualRegion($titlesByRegion[$regionName] ?? [], 'region:' . $regionName, $routing);
            }

            $this->seedShowcaseMisroutes($titlesByRegion);
        } finally {
            Carbon::setTestNow();
        }
    }

    /**
     * @return array<string, array<int, array{book: ManualBook, grade: string}>> keyed by region name
     */
    private function seedManualTitles(): array
    {
        $titles = [];

        foreach (ShowcaseData::MANUAL_TITLES as $index => $data) {
            $region = Region::query()->where('name', $data['region'])->firstOrFail();
            $title = "{$data['region']} Grade {$data['grade']} {$data['subject']}";
            $book = $this->persist(new ManualBook(), [
                'code' => sprintf('MT-%s-G%02d-%s', ShowcaseData::GEOGRAPHY[$data['region']]['code'], (int) $data['grade'], Str::upper(Str::substr(Str::slug($data['subject'], ''), 0, 4))),
                'title' => $title,
                'grade_name' => 'Grade ' . $data['grade'],
                'subject_name' => $data['subject'],
                'region_id' => $region->id,
                'grade_id' => Grade::query()->where('name', $data['grade'])->value('id'),
                'subject_id' => Subject::query()->where('name', $data['subject'])->value('id'),
                'has_teacher_guide' => $data['teacher_guide'],
                'students_per_package' => ShowcaseData::BOOKS_PER_PACKAGE,
                'notes' => 'Printed before barcode tracking; tracked by quantity.',
                'created_at' => $this->setupDate,
                'updated_at' => $this->setupDate,
            ]);

            $editions = $data['teacher_guide'] ? ['student' => [0, 'Student textbook'], 'teacher' => [1, 'Teacher guide']] : ['student' => [0, 'Student textbook']];
            foreach ($editions as $edition => [$digit, $label]) {
                $book->codes()->create([
                    'edition' => $edition,
                    'payload' => "{$title} {$label}",
                    'print_code' => sprintf('25%03d-%03d-%d-%04d', 100 + array_search($data['region'], array_keys(ShowcaseData::GEOGRAPHY), true) * 10, $index + 1, $digit, 1700 + $index),
                ]);
            }

            $titles[$data['region']][] = ['book' => $book, 'grade' => $data['grade']];
        }

        return $titles;
    }

    /**
     * Moves one region's titles down the chain with the real shipment service, so stock,
     * in-transit quantities, ledger entries and discrepancies come out exactly as in use.
     */
    private function shipManualRegion(array $titles, string $regionKey, array $routing): void
    {
        $shipments = app(ManualShipmentService::class);
        $quantities = [];
        foreach ($titles as $title) {
            $quantities[$title['book']->id] = $this->allocate(['kind' => 'primary', 'grade' => $title['grade']], $regionKey);
        }

        // Legs grouped by the level they deliver to, in travel order
        $hubKey = 'hub:' . $routing['hub'];
        $legs = ['hub' => [[$this->printerKeys['primary'], $hubKey]]];
        $warehouse = $routing['via_moe'] ? 'moe' : $regionKey;
        $legs[$routing['via_moe'] ? 'moe' : 'region'] = [[$hubKey, $warehouse]];
        if ($routing['via_moe']) {
            $legs['region'] = [['moe', $regionKey]];
        }
        foreach (['zone', 'woreda', 'school'] as $level) {
            foreach ($this->nodes as $key => $node) {
                if ($node['level'] === $level && ($level !== 'school' || $node['kind'] === 'primary')
                    && str_starts_with($this->ancestorAt($key, 'region'), $regionKey)) {
                    $legs[$level][] = [$node['parent'], $key];
                }
            }
        }

        $durations = ['hub' => $routing['hub'] === 'port' ? 18 : 3, 'moe' => 3, 'region' => 6, 'zone' => 4, 'woreda' => 3, 'school' => 2];
        $levels = array_keys($legs);
        $reachedAt = array_search($routing['reached'], $levels, true);
        $transitLevel = $routing['transit'] ? ($levels[$reachedAt + 1] ?? null) : null;

        // Work backwards from now so the latest event lands `days_ago` days back
        $span = 0;
        foreach (array_slice($levels, 0, $reachedAt + 1) as $level) {
            $span += $durations[$level] + 2;
        }
        $clock = $this->now->copy()->subDays($span + $routing['days_ago'] + ($transitLevel ? 2 : 0))->setTime(8, 30);
        $legIndex = 0;

        foreach ($levels as $level) {
            if ($level !== $transitLevel && array_search($level, $levels, true) > $reachedAt) {
                break;
            }

            $dispatchedAt = $clock->copy()->addDays(2);
            $arrivedAt = $dispatchedAt->copy()->addDays($durations[$level]);

            foreach ($legs[$level] as [$fromKey, $toKey]) {
                $lines = [];
                foreach ($titles as $title) {
                    $quantity = $quantities[$title['book']->id][$toKey === 'moe' || str_starts_with($toKey, 'hub:') ? $regionKey : $toKey] ?? 0;
                    if ($quantity > 0) {
                        $lines[] = $this->packLine($title['book'], $quantity);
                    }
                }

                if ($level === 'zone') {
                    $this->sortShowcasePackages($fromKey, $toKey, $lines, $dispatchedAt);
                }

                Carbon::setTestNow($dispatchedAt->copy()->addMinutes(mt_rand(0, 240)));
                $shipment = $shipments->dispatch($this->nodes[$fromKey]['user'], [
                    'from_organization_id' => $this->nodes[$fromKey]['org']->id,
                    'to_organization_id' => $this->nodes[$toKey]['org']->id,
                    'lines' => $lines,
                    'carrier' => $level === 'hub' ? ShowcaseData::LOGISTICS_HUBS[$routing['hub']]['carrier'] : 'Contracted truck',
                    'vehicle_no' => $level === 'hub' ? null : sprintf('3-%s%05d', ['A', 'B', 'C'][mt_rand(0, 2)], mt_rand(10000, 99999)),
                    'driver_name' => $level === 'hub' ? null : $this->personName(),
                    'waybill_no' => sprintf('WB-%s-%04d', $this->now->format('y'), ++$legIndex),
                    'expected_at' => $arrivedAt->copy(),
                ]);

                if ($level === $transitLevel) {
                    continue;
                }

                $counts = $shipment->lines->mapWithKeys(fn ($line) => [$line->id => [
                    'full_packages' => $line->full_packages,
                    'partial_package_books' => $line->partial_package_books,
                    'partial_teacher_guides' => $line->partial_teacher_guides,
                ]])->all();
                $counts = $this->withShowcaseDiscrepancy($counts, $shipment, $level, $legIndex);

                Carbon::setTestNow($arrivedAt->copy()->addMinutes(mt_rand(0, 240)));
                $received = $shipments->receive($this->nodes[$toKey]['user'], $shipment, $counts);

                $this->resolveShowcaseDiscrepancies($received, $toKey, $arrivedAt);
            }

            $clock = $arrivedAt;
        }
    }

    // The region warehouse scans every package into its zone bay the day before the trucks leave
    private function sortShowcasePackages(string $fromKey, string $toKey, array $lines, Carbon $dispatchedAt): void
    {
        $zoneName = Zone::query()->whereKey($this->nodes[$toKey]['org']->zone_id)->value('name');
        $clock = $dispatchedAt->copy()->subDay()->setTime(9, mt_rand(0, 59));

        foreach ($lines as $line) {
            $book = ManualBook::query()->find($line['manual_book_id']);
            $packages = $line['full_packages'] + ($line['partial_package_books'] || $line['partial_teacher_guides'] ? 1 : 0);

            for ($package = 0; $package < $packages; $package++) {
                Carbon::setTestNow($clock->addSeconds(mt_rand(8, 25))->copy());
                $this->scanner()->record($this->nodes[$fromKey]['user'], $this->nodes[$fromKey]['org'], Str::upper($zoneName), ManualScan::CONTEXT_SORT, ['title' => $book]);
            }
        }
    }

    /**
     * One misroute caught while sorting in Oromia and since sorted out, and one caught a few
     * hours ago while a woreda office was unloading a truck that is still being received.
     */
    private function seedShowcaseMisroutes(array $titlesByRegion): void
    {
        $bureau = 'region:Oromia';
        $sortedAt = ManualScan::query()->where('organization_id', $this->nodes[$bureau]['org']->id)->min('scanned_at');
        $foreignZone = Zone::query()->whereHas('region', fn ($region) => $region->where('name', '!=', 'Oromia'))->where('is_subcity', false)->orderBy('name')->value('name');

        if ($sortedAt && $foreignZone) {
            Carbon::setTestNow(Carbon::parse($sortedAt)->addMinutes(37));
            $misrouted = $this->scanner()->record($this->nodes[$bureau]['user'], $this->nodes[$bureau]['org'], Str::upper($foreignZone), ManualScan::CONTEXT_SORT, [
                'title' => $titlesByRegion['Oromia'][0]['book'],
            ]);

            Carbon::setTestNow(Carbon::parse($sortedAt)->addDay()->setTime(10, 15));
            $this->scanner()->resolve($this->moeOfficer, $misrouted, "Package carried {$foreignZone} label but held Oromia books. Relabelled and kept in Oromia stock.");
        }

        $shipment = ManualDistribution::query()
            ->where('status', ManualDistribution::STATUS_DISPATCHED)
            ->whereHas('destinationOrganization', fn ($organization) => $organization->whereNotNull('zone_id'))
            ->with(['lines.book', 'destinationOrganization'])
            ->orderBy('id')
            ->first();

        if (! $shipment) {
            return;
        }

        $destination = $shipment->destinationOrganization;
        $receiverKey = collect($this->nodes)->search(fn ($node) => $node['org']->id === $destination->id);
        $receiver = $this->nodes[$receiverKey]['user'];
        $ownZone = Zone::query()->find($destination->zone_id);
        $otherZone = Zone::query()->where('region_id', $ownZone->region_id)->whereKeyNot($ownZone->id)->value('name');
        $clock = $this->now->copy()->subHours(3);

        // About half the truck unloaded so far
        foreach ($shipment->lines as $line) {
            for ($package = 0; $package < intdiv($line->full_packages + 1, 2); $package++) {
                Carbon::setTestNow($clock->addSeconds(mt_rand(10, 30))->copy());
                $this->scanner()->record($receiver, $destination, Str::upper($ownZone->name), ManualScan::CONTEXT_RECEIVE, ['shipment' => $shipment, 'title' => $line->book]);
            }
        }

        if ($otherZone) {
            Carbon::setTestNow($clock->addMinute()->copy());
            $this->scanner()->record($receiver, $destination, Str::upper($otherZone), ManualScan::CONTEXT_RECEIVE, ['shipment' => $shipment, 'title' => $shipment->lines->first()->book]);
        }
    }

    private function scanner(): ManualScanService
    {
        return $this->scanService ??= app(ManualScanService::class);
    }

    /**
     * @return array{manual_book_id: int, full_packages: int, partial_package_books: int, partial_teacher_guides: int}
     */
    private function packLine(ManualBook $book, int $studentBooks): array
    {
        $partial = $studentBooks % $book->students_per_package;

        return [
            'manual_book_id' => $book->id,
            'full_packages' => intdiv($studentBooks, $book->students_per_package),
            'partial_package_books' => $partial,
            'partial_teacher_guides' => $partial > 0 && $book->has_teacher_guide ? 1 : 0,
        ];
    }

    // A few realistic receiving problems on school and woreda deliveries
    private function withShowcaseDiscrepancy(array $counts, ManualDistribution $shipment, string $level, int $legIndex): array
    {
        $lineId = array_key_first($counts);

        if ($level === 'school' && $legIndex % 4 === 0 && $counts[$lineId]['full_packages'] > 0) {
            $counts[$lineId]['full_packages']--; // one package did not arrive
        } elseif ($level === 'school' && $legIndex % 4 === 1) {
            $counts[$lineId]['damaged_quantity'] = min(5, $counts[$lineId]['full_packages'] * 40); // water damage
        } elseif ($level === 'woreda' && $legIndex % 3 === 0) {
            $counts[$lineId]['partial_teacher_guides']++; // one teacher guide too many
        }

        return $counts;
    }

    // Supervisors (the zone for schools, the region otherwise) close most problems; some stay open
    private function resolveShowcaseDiscrepancies(ManualDistribution $shipment, string $toKey, Carbon $arrivedAt): void
    {
        $discrepancies = $shipment->lines->flatMap->discrepancies;

        // Every third problem shipment is left open, so the queue is never empty whatever the random draws
        if ($discrepancies->isEmpty() || $this->problemShipments++ % 3 === 0) {
            return;
        }

        $supervisor = $this->nodes[$this->ancestorAt($toKey, $this->nodes[$toKey]['level'] === 'school' ? 'zone' : 'region')]['user'];
        $resolution = ['missing' => 'found', 'damaged' => 'written_off', 'excess' => 'corrected'];

        Carbon::setTestNow($arrivedAt->copy()->addDays(mt_rand(2, 6)));
        foreach ($discrepancies as $discrepancy) {
            app(ManualShipmentService::class)->resolve($supervisor, $discrepancy, $resolution[$discrepancy->type], 'Checked during a supervision visit.');
        }
    }

    private function ancestorAt(string $key, string $level): string
    {
        while ($this->nodes[$key]['level'] !== $level && $this->nodes[$key]['parent']) {
            $key = $this->nodes[$key]['parent'];
        }

        return $key;
    }

    // ---------------------------------------------------------------------
    // School records: enrolment, student register, books in students' hands
    // ---------------------------------------------------------------------

    private function seedSchoolRecords(): void
    {
        $grades = Grade::query()->pluck('id', 'name');

        foreach ($this->nodes as $node) {
            if ($node['level'] !== 'school') {
                continue;
            }

            foreach (array_keys(ShowcaseData::ENROLMENT) as $grade) {
                if (((int) $grade <= 8) !== ($node['kind'] === 'primary')) {
                    continue;
                }

                // Same size as the demand that drove shipments, so required and delivered line up
                $total = (int) round(ShowcaseData::ENROLMENT[$grade] * $node['factor']);
                $male = (int) round($total * mt_rand(48, 53) / 100);

                SchoolEnrolment::query()->create([
                    'organization_id' => $node['org']->id,
                    'academic_year' => $this->ecYear,
                    'grade_id' => $grades[$grade],
                    'male' => $male,
                    'female' => $total - $male,
                    'sections' => mt_rand(1, 4) === 1 ? (int) ceil($total / 45) : null,
                    'created_at' => $this->now->copy()->subDays(50),
                    'updated_at' => $this->now->copy()->subDays(50),
                ]);
            }
        }

        try {
            foreach ($this->nodes as $key => $node) {
                if ($node['level'] === 'school' && $node['kind'] === 'primary' && $this->ancestorAt($key, 'region') === 'region:Oromia') {
                    $this->seedStudentsAndIssues($node, $grades);
                }
            }

            $this->seedVerificationVisits();
        } finally {
            Carbon::setTestNow();
        }
    }

    /**
     * Woreda supervisors have visited most Oromia primary schools: most counts match, one
     * shelf is short and one student could not show a book. The last school is not visited yet.
     */
    private function seedVerificationVisits(): void
    {
        $verification = app(VerificationService::class);
        $schools = collect($this->nodes)
            ->filter(fn ($node, $key) => $node['level'] === 'school' && $node['kind'] === 'primary' && $this->ancestorAt($key, 'region') === 'region:Oromia')
            ->values();

        foreach ($schools->slice(0, -1)->values() as $index => $node) {
            $school = $node['org'];
            Carbon::setTestNow($this->now->copy()->subDays(12 - $index * 2)->setTime(10, 30));

            $shelf = ManualStockBalance::query()->where('organization_id', $school->id)->where('edition', 'student')
                ->pluck('on_hand', 'manual_book_id')
                ->map(fn ($onHand) => (int) $onHand)
                ->all();
            $shortTitle = null;
            if ($index === 1 && $shelf) {
                $shelf[array_key_first($shelf)] = max(0, reset($shelf) - 3);
                $shortTitle = ManualBook::query()->whereKey(array_key_first($shelf))->value('title');
            }

            $checks = [];
            foreach ($verification->sampleStudents($school, $this->ecYear, 8) as $position => $studentId) {
                $held = StudentBookIssue::query()->where('student_id', $studentId)->whereNull('returned_at')->pluck('manual_book_id');
                $checks[$studentId] = $held->mapWithKeys(fn ($bookId, $i) => [$bookId => ! ($index === 2 && $position === 0 && $i === 0)])->all();
            }

            $verification->record($this->nodes[$node['parent']]['user'], $school, $this->ecYear, $shelf, $checks, match ($index) {
                1 => "Three copies of {$shortTitle} missing from the store room; director to search and report.",
                2 => 'One student said the book was left at home. Follow up next visit.',
                default => 'Store room dry and locked. Records in order.',
            });
        }
    }

    private function seedStudentsAndIssues(array $school, $grades): void
    {
        $books = app(SchoolBookService::class);
        $director = $school['user'];
        $titles = ManualBook::query()->where('region_id', $school['org']->region_id)->get()->groupBy('grade_id');
        $deliveredAt = Carbon::parse(ManualDistribution::query()
            ->where('destination_organization_id', $school['org']->id)
            ->max('received_at') ?? $this->now->copy()->subDays(30));

        foreach ($titles as $gradeId => $gradeTitles) {
            $grade = $grades->search($gradeId);
            $enrolment = SchoolEnrolment::query()
                ->where('organization_id', $school['org']->id)
                ->where('academic_year', $this->ecYear)
                ->where('grade_id', $gradeId)
                ->first();
            if (! $enrolment) {
                continue;
            }

            $rows = [];
            for ($n = 0; $n < $enrolment->total(); $n++) {
                $rows[] = [
                    'organization_id' => $school['org']->id,
                    'academic_year' => $this->ecYear,
                    'student_no' => sprintf('%s-%02d-%04d', $school['code'], $grade, $n + 1),
                    'name' => $this->personName(),
                    'sex' => $n < $enrolment->male ? 'M' : 'F',
                    'grade_id' => $gradeId,
                    'section' => chr(65 + intdiv($n, SchoolEnrolment::STUDENTS_PER_SECTION)),
                    'status' => Student::STATUS_ACTIVE,
                    'created_at' => $deliveredAt,
                    'updated_at' => $deliveredAt,
                ];
            }
            Student::query()->insert($rows);
            $students = Student::query()->where('organization_id', $school['org']->id)->where('grade_id', $gradeId)->get();

            // A few students are absent on hand-out day
            $present = $students->filter(fn () => mt_rand(1, 100) <= 93);
            foreach ($gradeTitles as $title) {
                $onHand = (int) ManualStockBalance::query()
                    ->where('organization_id', $school['org']->id)
                    ->where('manual_book_id', $title->id)
                    ->where('edition', ManualBook::EDITION_STUDENT)
                    ->value('on_hand');

                Carbon::setTestNow($deliveredAt->copy()->addDays(mt_rand(2, 5))->setTime(9, 0));
                $assignments = $present->take($onHand)->mapWithKeys(fn ($student) => [$student->id => [$title->id]])->all();
                if ($assignments) {
                    $books->issueToStudents($director, $school['org'], $assignments);
                }

                $guides = ! $title->has_teacher_guide ? 0 : min($enrolment->effectiveSections(), (int) ManualStockBalance::query()
                    ->where('organization_id', $school['org']->id)
                    ->where('manual_book_id', $title->id)
                    ->where('edition', ManualBook::EDITION_TEACHER)
                    ->value('on_hand'));
                for ($section = 1; $section <= $guides; $section++) {
                    $books->issueTeacherGuides($director, $school['org'], $title, $this->ecYear, $this->personName(), chr(64 + $section));
                }
            }

            // One transfer per grade: the student hands the books back before leaving
            $leaver = $present->first();
            if ($leaver) {
                Carbon::setTestNow($deliveredAt->copy()->addDays(mt_rand(10, 20))->setTime(11, 0));
                foreach (StudentBookIssue::query()->where('student_id', $leaver->id)->whereNull('returned_at')->get() as $issue) {
                    $books->returnFromStudent($director, $issue, 'good');
                }
                $leaver->update(['status' => 'transferred']);
            }
        }
    }

    // ---------------------------------------------------------------------
    // Barcode / QR images (same layout AddPrintOrder writes)
    // ---------------------------------------------------------------------

    private function writeCodeImages(): void
    {
        $disk = Storage::disk('public');
        $qrWriter = new DNS2D();
        $barcodeWriter = new DNS1D();

        foreach ($this->imageQueue as ['order' => $order, 'codes' => $codes, 'title' => $title]) {
            foreach (array_slice($codes, 0, self::IMAGE_PACKAGES_PER_ORDER, true) as $packageKey => $package) {
                $directory = 'printOrders/' . $order->id . '/' . $packageKey;
                $qr = Str::before($package['QR'], '.svg');

                $disk->put($directory . '/' . $package['QR'], $qrWriter->getBarcodeSVG($title . ': ' . $qr, 'QRCODE'));

                foreach ($package['barcodes'] as $file) {
                    $disk->put($directory . '/barcods/' . $file, $barcodeWriter->getBarcodeSVG(Str::before($file, '.svg'), 'C39', 1, 50));
                }
            }
        }
    }

    // ---------------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------------

    /**
     * Package map in the format AddPrintOrder stores: [n => [barcodes => [...svg], QR => svg, status => 1]].
     */
    private function bookCodes(int $barcodeStart, int $qrcodeStart, int $copies): array
    {
        $codes = [];
        $packages = (int) ceil($copies / ShowcaseData::BOOKS_PER_PACKAGE);

        for ($package = 0; $package < $packages; $package++) {
            $first = $package * ShowcaseData::BOOKS_PER_PACKAGE;
            $last = min($copies, $first + ShowcaseData::BOOKS_PER_PACKAGE) - 1;
            $barcodes = [];

            for ($copy = $first; $copy <= $last; $copy++) {
                $barcodes[] = str_pad((string) ($barcodeStart + $copy), 13, '0', STR_PAD_LEFT) . '.svg';
            }

            $codes[$package + 1] = [
                'barcodes' => $barcodes,
                'QR' => str_pad((string) ($qrcodeStart + $package), 13, '0', STR_PAD_LEFT) . '.svg',
                'status' => 1,
            ];
        }

        return $codes;
    }

    // 13 digits: Ethiopian year (4) + order sequence (3) + copy number (6)
    private function barcode(int $sequence, int $copy): int
    {
        return (int) sprintf('%04d%03d%06d', $this->ecYear, $sequence, $copy + 1);
    }

    // 13 digits: 8 + Ethiopian year (4) + order sequence (3) + package number (5)
    private function qrcode(int $sequence, int $package): int
    {
        return (int) sprintf('8%04d%03d%05d', $this->ecYear, $sequence, $package + 1);
    }

    // ISBN-13 in Ethiopia's 978-99944 registration group, with a valid check digit
    private function isbn(int $publisher, int $title): string
    {
        $digits = sprintf('97899944%d%03d', $publisher, $title);
        $sum = 0;
        foreach (str_split($digits) as $index => $digit) {
            $sum += (int) $digit * ($index % 2 === 0 ? 1 : 3);
        }

        return sprintf('978-99944-%d-%03d-%d', $publisher, $title, (10 - $sum % 10) % 10);
    }

    private function stageLevel(string $stage): ?string
    {
        return match ($stage) {
            'requested', 'accepted' => null,
            'printed' => 'printer',
            default => $stage,
        };
    }

    private function roundUpToPackage(int $copies): int
    {
        return (int) ceil($copies / ShowcaseData::BOOKS_PER_PACKAGE) * ShowcaseData::BOOKS_PER_PACKAGE;
    }

    private function personName(): string
    {
        $index = $this->personIndex++;
        $first = ShowcaseData::FIRST_NAMES[$index % count(ShowcaseData::FIRST_NAMES)];
        $father = ShowcaseData::FATHER_NAMES[($index * 7 + intdiv($index, count(ShowcaseData::FIRST_NAMES))) % count(ShowcaseData::FATHER_NAMES)];

        return $first . ' ' . $father;
    }

    private function landline(string $area): string
    {
        return sprintf('+251 %s %03d %04d', ltrim($area, '0'), mt_rand(110, 899), mt_rand(0, 9999));
    }

    private function mobile(): string
    {
        return sprintf('09%02d%06d', mt_rand(10, 99), mt_rand(0, 999999));
    }

    private function jitter(array $gps): array
    {
        return [$gps[0] + mt_rand(-150, 150) / 10000, $gps[1] + mt_rand(-150, 150) / 10000];
    }

    private function pick(array $items, int $count): array
    {
        $picked = [];
        while (count($picked) < min($count, count($items))) {
            $picked[$items[mt_rand(0, count($items) - 1)]] = true;
        }

        return array_keys($picked);
    }

    private function lookup(string $model, string $column, string $value): ?int
    {
        return $this->lookupCache[$model][$column][$value] ??= $model::query()->where($column, $value)->value('id');
    }

    /**
     * Saves with explicit timestamps and columns outside $fillable (e.g. actual_print_time).
     *
     * @template T of Model
     * @param  T  $model
     * @return T
     */
    private function persist(Model $model, array $attributes): Model
    {
        $model->forceFill($attributes)->save();

        return $model;
    }

    private function printSummary(): void
    {
        if (! $this->command) {
            return;
        }

        $this->command->info('Showcase data seeded for academic year ' . $this->yearLabel . '.');
        $this->command->table(['Records', 'Count'], [
            ['Organizations / warehouses', count($this->nodes)],
            ['Routes', count($this->routes)],
            ['Print orders', PrintOrder::query()->count()],
            ['Package rows', Package::query()->count()],
            ['Distribution plans', Distribution::query()->count()],
            ['Student hand-outs', Delivery::query()->count()],
            ['Manual shipments', ManualDistribution::query()->count()],
            ['Students registered', Student::query()->count()],
            ['Books with students', StudentBookIssue::query()->whereNull('returned_at')->count()],
        ]);
        $this->command->line('Sample logins (password: ' . ShowcaseData::PASSWORD . '):');
        foreach (['moe.officer', 'oromia.bureau', 'east.shewa.zone', 'adama.woreda.01', 'empde.store'] as $local) {
            $this->command->line('  ' . $local . '@' . ShowcaseData::EMAIL_DOMAIN);
        }
    }
}
