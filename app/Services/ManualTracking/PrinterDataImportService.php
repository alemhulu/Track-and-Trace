<?php

namespace App\Services\ManualTracking;

use App\Models\Grade;
use App\Models\ManualTracking\ManualAllocation;
use App\Models\ManualTracking\ManualAllocationImport;
use App\Models\ManualTracking\ManualBook;
use App\Models\ManualTracking\ManualBookCode;
use App\Models\ManualTracking\ManualBookPackage;
use App\Models\Region;
use App\Models\Subject;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use OpenSpout\Common\Entity\Cell\FormulaCell;
use OpenSpout\Reader\XLSX\Options;
use OpenSpout\Reader\XLSX\Reader;

/**
 * Reads one region's printer data workbook (Printer_Data_Requirement-<REGION>.xlsx):
 *
 * - 1_Books: a row per book edition. Its QR code number ("25110-001-2-1682": print run and
 *   region, title number, 2 = student book / 1 = teacher guide, serial) and the exact QR
 *   text. The student book and teacher guide of one title number become one title.
 * - 2_Zone_Allocation: student books and teacher guides per zone and title. This is the
 *   region's allocation; the region total is the sum of its zones.
 * - 3_Package_List: a row per carton. Every carton for a zone carries the zone's package QR;
 *   cartons of one zone, title and content are stored as one package row with a count.
 *
 * Zones are matched by their code, then by name, and created when missing. Columns are found
 * by header name. Nothing is saved while any row has an error; a repeat import of the same
 * file changes nothing.
 */
class PrinterDataImportService
{
    public const SOURCE_PREFIX = 'printer:';

    /** @var \Illuminate\Support\Collection<string, Region>|null keyed by the name's letters */
    private $regions = null;

    private const BOOK_COLUMNS = [
        'region' => 'region',
        'code' => 'book qr code no.',
        'text' => 'qr code text',
        'grade' => 'grade',
        'subject' => 'subject',
        'language' => 'language',
        'edition' => 'edition',
        'paired' => 'paired student book code',
        'per_carton' => 'books per carton',
        'title' => 'title',
        'isbn' => 'isbn',
        'print_year' => 'print year / edition',
        'copies' => 'total printed copies',
    ];

    private const ZONE_COLUMNS = [
        'zone' => 'zone name',
        'zone_code' => 'zone code',
        'student_code' => 'student book code',
        'teacher_code' => 'teacher guide code',
        'student' => 'student books',
        'teacher' => 'teacher guides',
        'delivery_point' => 'delivery point',
        'cartons' => 'total cartons',
    ];

    private const PACKAGE_COLUMNS = [
        'qr_code' => 'package qr code no.',
        'qr_text' => 'package qr text',
        'zone' => 'zone name',
        'zone_code' => 'zone code',
        'type' => 'carton type',
        'student_code' => 'student book code',
        'student' => 'student books in carton',
        'teacher_code' => 'teacher guide code',
        'teacher' => 'teacher guides in carton',
    ];

    /** Tables whose rows point at a manual book; an imported book is only removed when none does */
    private const BOOK_REFERENCES = [
        'manual_book_packages', 'manual_distribution_lines', 'manual_scans', 'manual_stock_balances',
        'manual_stock_ledgers', 'student_book_issues', 'teacher_guide_issues', 'verification_visit_items',
    ];

    /**
     * @return array{
     *     region: ?string, region_id: ?int,
     *     books: array<string, array>, codes: array<string, string>,
     *     zones: array<string, array{name: string, label: string, zone_id: ?int, qr_code: ?string, qr_text: ?string, delivery_point: ?string, titles: int, student: int, teacher: int, cartons: int}>,
     *     zone_rows: array<string, array<string, array<string, array{quantity: int, row: int}>>>,
     *     packages: array<string, array>,
     *     title_count: int, line_count: int, student: int, teacher: int, cartons: int, total: int,
     *     warnings: array<int, string>, errors: array<int, string>
     * }
     */
    public function parse(string $path): array
    {
        $this->regions = null;
        $result = [
            'region' => null, 'region_id' => null, 'books' => [], 'codes' => [], 'zones' => [], 'zone_rows' => [], 'packages' => [],
            'title_count' => 0, 'line_count' => 0, 'student' => 0, 'teacher' => 0, 'cartons' => 0, 'total' => 0,
            'warnings' => [], 'errors' => [],
        ];

        $options = new Options();
        $options->SHOULD_FORMAT_DATES = false;
        $reader = new Reader($options);
        $reader->open($path);

        $seen = [];
        try {
            foreach ($reader->getSheetIterator() as $sheet) {
                $name = Str::lower(trim($sheet->getName()));
                $handler = match ($name) {
                    '1_books' => 'readBook',
                    '2_zone_allocation' => 'readZoneLine',
                    '3_package_list' => 'readCarton',
                    default => null,
                };

                if (! $handler) {
                    continue;
                }

                // The zone and package sheets refer to the books, so 1_Books must come first
                if ($handler !== 'readBook' && ! isset($seen['readBook'])) {
                    $result['errors'][] = "Sheet \"{$sheet->getName()}\" comes before 1_Books.";
                    break;
                }

                $seen[$handler] = true;
                $columns = null;
                foreach ($sheet->getRowIterator() as $index => $row) {
                    $values = array_map(fn ($cell) => $this->cellValue($cell), $row->getCells());

                    if ($columns === null) {
                        $columns = $this->columns($values, $handler, $sheet->getName(), $result);
                        if ($columns === false) {
                            break;
                        }

                        continue;
                    }

                    $line = [];
                    foreach ($columns as $field => $column) {
                        $line[$field] = is_string($values[$column] ?? null) ? trim(preg_replace('/\s+/u', ' ', $values[$column])) : ($values[$column] ?? null);
                    }

                    $this->{$handler}($line, $index, $result);
                }
            }
        } finally {
            $reader->close();
        }

        foreach (['readBook' => '1_Books', 'readZoneLine' => '2_Zone_Allocation', 'readCarton' => '3_Package_List'] as $handler => $sheet) {
            if (! isset($seen[$handler]) && ! $result['errors']) {
                $result['errors'][] = "The workbook has no {$sheet} sheet.";
            }
        }

        if ($result['errors']) {
            return $result;
        }

        $this->finish($result);

        return $result;
    }

    /**
     * Saves the region's titles, book QR codes, allocation and cartons for the year. The region's
     * allocation and printer cartons are replaced; titles no longer listed are removed when
     * nothing else refers to them.
     */
    public function commit(?User $actor, string $path, string $fileName, string $year): array
    {
        $result = $this->parse($path);

        if ($result['errors']) {
            return $result + ['import' => null];
        }

        $result['import'] = DB::transaction(function () use ($actor, &$result, $fileName, $year): ManualAllocationImport {
            $region = Region::query()->findOrFail($result['region_id']);

            $import = ManualAllocationImport::create([
                'file_name' => $fileName,
                'academic_year' => $year,
                'level' => 'region',
                'imported_by' => $actor?->id,
                'line_count' => $result['line_count'],
                'title_count' => $result['title_count'],
                'total_quantity' => $result['total'],
                'region_totals' => [
                    'source' => 'printer',
                    'regions' => [$region->name => [
                        'lines' => $result['line_count'], 'titles' => $result['title_count'],
                        'student' => $result['student'], 'teacher' => $result['teacher'], 'cartons' => $result['cartons'],
                    ]],
                    'zones' => [$region->name => collect($result['zones'])->mapWithKeys(fn ($zone) => [$zone['name'] => [
                        'zone_id' => $zone['zone_id'], 'lines' => $zone['titles'], 'titles' => $zone['titles'],
                        'student' => $zone['student'], 'teacher' => $zone['teacher'], 'cartons' => $zone['cartons'],
                    ]])->all()],
                ],
                'warnings' => $result['warnings'],
            ]);

            $zoneIds = $this->saveZones($region, $result['zones']);
            foreach ($zoneIds as $code => $id) {
                $result['zones'][$code]['zone_id'] = $id;
            }

            $bookIds = [];
            foreach ($result['books'] as $series => $book) {
                $bookIds[$series] = $this->saveBook($region, $series, $book)->id;
            }

            $this->saveAllocation($import, $region, $year, $result, $zoneIds, $bookIds);
            $this->savePackages($region, $year, $result['packages'], $result['zones'], $bookIds);
            $this->removeUnlistedBooks($region, array_values($bookIds));

            return $import;
        });

        return $result;
    }

    // ---- Reading -------------------------------------------------------------------------

    /**
     * @return array<string, int>|false column index per field, or false when a required header is missing
     */
    private function columns(array $values, string $handler, string $sheet, array &$result): array|false
    {
        $wanted = match ($handler) {
            'readBook' => self::BOOK_COLUMNS,
            'readZoneLine' => self::ZONE_COLUMNS,
            default => self::PACKAGE_COLUMNS,
        };

        $headers = [];
        foreach ($values as $index => $value) {
            $headers[Str::lower(trim(preg_replace('/\s+/u', ' ', (string) $value)))] ??= $index;
        }

        $columns = [];
        $missing = false;
        $optional = ['language', 'paired', 'per_carton', 'isbn', 'print_year', 'delivery_point', 'cartons', 'title'];
        foreach ($wanted as $field => $header) {
            if (isset($headers[$header])) {
                $columns[$field] = $headers[$header];
            } elseif (! in_array($field, $optional, true)) {
                $result['errors'][] = "Sheet \"{$sheet}\": no \"{$header}\" column in the first row.";
                $missing = true;
            }
        }

        return $missing ? false : $columns;
    }

    private function readBook(array $line, int $row, array &$result): void
    {
        $code = (string) ($line['code'] ?? '');
        if ($code === '') {
            return;
        }

        $where = "1_Books row {$row}";
        $region = $this->region((string) $line['region'], $where, $result);
        if (! $region) {
            return;
        }

        if (isset($result['codes'][$code])) {
            $result['errors'][] = "{$where}: book code {$code} is listed twice.";

            return;
        }

        $edition = $this->edition((string) $line['edition'], $code);
        $series = $edition === ManualBook::EDITION_TEACHER && filled($line['paired'] ?? null)
            ? $this->series((string) $line['paired'])
            : $this->series($code);

        if (isset($result['books'][$series]['editions'][$edition])) {
            $other = $result['books'][$series]['editions'][$edition]['code'];
            $result['errors'][] = "{$where}: {$code} and {$other} are both the " . ($edition === ManualBook::EDITION_TEACHER ? 'teacher guide' : 'student book') . " of title {$series}.";

            return;
        }

        if ((string) ($line['text'] ?? '') === '') {
            $result['errors'][] = "{$where}: book code {$code} has no QR code text.";

            return;
        }

        $result['codes'][$code] = $series;
        $book = $result['books'][$series] ?? [
            'grade' => (string) $line['grade'],
            'subject' => (string) $line['subject'],
            'language' => (string) ($line['language'] ?? ''),
            'title' => null,
            'isbn' => null,
            'print_year' => null,
            'per_carton' => null,
            'editions' => [],
        ];

        // The student book's title, grade and subject are the title's; the guide's fill the gaps
        $title = preg_replace('/\s+(student|teacher)\'?s?\s+(book|guide|text\s*book|textbook)\s*$/i', '', (string) ($line['title'] ?? ''));
        if ($title === '') {
            $title = trim(((string) $line['subject']) . ' ' . ((string) $line['grade']));
        }
        if ($edition === ManualBook::EDITION_STUDENT || $book['title'] === null) {
            $book['title'] = $title;
            $book['grade'] = (string) $line['grade'];
            $book['subject'] = (string) $line['subject'];
            $book['language'] = (string) ($line['language'] ?? '') ?: $book['language'];
        }

        $book['isbn'] ??= filled($line['isbn'] ?? null) ? (string) $line['isbn'] : null;
        $book['print_year'] ??= filled($line['print_year'] ?? null) ? (string) $line['print_year'] : null;
        if ($edition === ManualBook::EDITION_STUDENT && is_numeric($line['per_carton'] ?? null)) {
            $book['per_carton'] = (int) $line['per_carton'];
        }

        $book['editions'][$edition] = [
            'code' => $code,
            'text' => (string) $line['text'],
            'copies' => is_numeric($line['copies'] ?? null) ? (int) $line['copies'] : 0,
            'row' => $row,
        ];

        $result['books'][$series] = $book;
    }

    private function readZoneLine(array $line, int $row, array &$result): void
    {
        $studentCode = (string) ($line['student_code'] ?? '');
        $teacherCode = (string) ($line['teacher_code'] ?? '');
        if ($studentCode === '' && $teacherCode === '') {
            return;
        }

        $where = "2_Zone_Allocation row {$row}";
        $series = $this->seriesOf($studentCode, $teacherCode, $where, $result);
        $zoneCode = $this->zone($line, $where, $result);
        if ($series === null || $zoneCode === null) {
            return;
        }

        if (filled($line['delivery_point'] ?? null)) {
            $result['zones'][$zoneCode]['delivery_point'] ??= (string) $line['delivery_point'];
        }

        foreach ([ManualBook::EDITION_STUDENT => 'student', ManualBook::EDITION_TEACHER => 'teacher'] as $edition => $field) {
            $quantity = $line[$field] ?? null;
            if ($quantity === null || $quantity === '') {
                continue;
            }

            if (! is_numeric($quantity) || (float) $quantity < 0 || floor((float) $quantity) != (float) $quantity) {
                $result['errors'][] = "{$where}: \"{$quantity}\" is not a number of copies.";

                continue;
            }

            if ((int) $quantity === 0) {
                continue;
            }

            if (! isset($result['books'][$series]['editions'][$edition])) {
                $result['errors'][] = "{$where}: title {$series} has no " . ($field === 'teacher' ? 'teacher guide' : 'student book') . ' in 1_Books.';

                continue;
            }

            if (isset($result['zone_rows'][$zoneCode][$series][$edition])) {
                $result['warnings'][] = "{$where}: title {$series} is listed twice for zone {$zoneCode}; the copies are added.";
            }

            $result['zone_rows'][$zoneCode][$series][$edition]['quantity'] = ($result['zone_rows'][$zoneCode][$series][$edition]['quantity'] ?? 0) + (int) $quantity;
            $result['zone_rows'][$zoneCode][$series][$edition]['row'] ??= $row;
        }
    }

    private function readCarton(array $line, int $row, array &$result): void
    {
        $studentCode = (string) ($line['student_code'] ?? '');
        $teacherCode = (string) ($line['teacher_code'] ?? '');
        if ($studentCode === '' && $teacherCode === '') {
            return;
        }

        $where = "3_Package_List row {$row}";
        $series = $this->seriesOf($studentCode, $teacherCode, $where, $result);
        $zoneCode = $this->zone($line, $where, $result);
        if ($series === null || $zoneCode === null) {
            return;
        }

        $student = (int) ($line['student'] ?? 0);
        $teacher = (int) ($line['teacher'] ?? 0);
        if ($student + $teacher <= 0) {
            $result['errors'][] = "{$where}: the carton holds no books.";

            return;
        }

        $zone = &$result['zones'][$zoneCode];
        $qrCode = (string) ($line['qr_code'] ?? '');
        $qrText = (string) ($line['qr_text'] ?? '');
        if ($qrCode !== '' && ($zone['qr_code'] ?? null) !== null && $zone['qr_code'] !== $qrCode && ! ($zone['qr_warned'] ?? false)) {
            $result['warnings'][] = "{$where}: zone {$zoneCode} cartons carry two package QR codes ({$zone['qr_code']} and {$qrCode}); the first is kept.";
            $zone['qr_warned'] = true;
        }
        $zone['qr_code'] ??= $qrCode !== '' ? $qrCode : null;
        $zone['qr_text'] ??= $qrText !== '' ? $qrText : null;
        $zone['cartons']++;
        unset($zone);

        $type = (string) ($line['type'] ?? '') ?: 'Carton';
        $key = implode('|', [$zoneCode, $series, $type, $student, $teacher]);
        $result['packages'][$key] ??= [
            'zone_code' => $zoneCode, 'series' => $series, 'type' => $type,
            'student' => $student, 'teacher' => $teacher, 'count' => 0,
        ];
        $result['packages'][$key]['count']++;
    }

    /**
     * Totals, and the printer's own checks: the zones add up to the printed copies, and the
     * cartons hold the zone's copies.
     */
    private function finish(array &$result): void
    {
        if (! $result['books']) {
            $result['errors'][] = '1_Books lists no book.';

            return;
        }

        $zoneSums = [];
        foreach ($result['zone_rows'] as $zoneCode => $titles) {
            $zone = &$result['zones'][$zoneCode];
            $zone['titles'] = count($titles);
            foreach ($titles as $series => $editions) {
                foreach ($editions as $edition => $row) {
                    $zoneSums[$series][$edition] = ($zoneSums[$series][$edition] ?? 0) + $row['quantity'];
                    $zone[$edition === ManualBook::EDITION_TEACHER ? 'teacher' : 'student'] += $row['quantity'];
                    $result['line_count']++;
                }
            }
            unset($zone);
        }

        $mismatches = 0;
        foreach ($result['books'] as $series => $book) {
            foreach ($book['editions'] as $edition => $copy) {
                $allocated = $zoneSums[$series][$edition] ?? 0;
                if ($allocated !== $copy['copies'] && $mismatches++ < 20) {
                    $result['warnings'][] = "Book {$copy['code']}: " . number_format($copy['copies']) . ' printed but ' . number_format($allocated) . ' allocated to zones.';
                }
            }
        }
        if ($mismatches > 20) {
            $result['warnings'][] = ($mismatches - 20) . ' more books differ between printed and allocated copies.';
        }

        $packed = [];
        foreach ($result['packages'] as $package) {
            $key = $package['zone_code'] . '|' . $package['series'];
            $packed[$key]['student'] = ($packed[$key]['student'] ?? 0) + $package['student'] * $package['count'];
            $packed[$key]['teacher'] = ($packed[$key]['teacher'] ?? 0) + $package['teacher'] * $package['count'];
        }

        $mismatches = 0;
        foreach ($result['zone_rows'] as $zoneCode => $titles) {
            foreach ($titles as $series => $editions) {
                foreach (['student', 'teacher'] as $edition) {
                    $allocated = $editions[$edition]['quantity'] ?? 0;
                    $inCartons = $packed["{$zoneCode}|{$series}"][$edition] ?? 0;
                    if ($allocated !== $inCartons && $mismatches++ < 20) {
                        $result['warnings'][] = "Zone {$zoneCode}, title {$series}: " . number_format($allocated) . " {$edition} copies allocated but " . number_format($inCartons) . ' packed in cartons.';
                    }
                }
            }
        }
        if ($mismatches > 20) {
            $result['warnings'][] = ($mismatches - 20) . ' more zone lines differ between allocated and packed copies.';
        }

        // Several language editions of one subject and grade share a title; the title number tells them apart
        $byTitle = collect($result['books'])->groupBy(fn ($book) => Str::lower($book['title']), true);
        foreach ($byTitle as $books) {
            if ($books->count() > 1) {
                foreach ($books->keys() as $series) {
                    $result['books'][$series]['title'] .= " ({$series})";
                }
            }
        }

        foreach ($result['zones'] as $code => $zone) {
            $result['zones'][$code]['name'] = $this->zoneName($zone['label']);
            unset($result['zones'][$code]['qr_warned']);
        }
        ksort($result['zones']);

        $result['title_count'] = count($result['books']);
        $result['student'] = (int) collect($result['zones'])->sum('student');
        $result['teacher'] = (int) collect($result['zones'])->sum('teacher');
        $result['cartons'] = (int) collect($result['zones'])->sum('cartons');
        $result['total'] = $result['student'] + $result['teacher'];
    }

    private function region(string $name, string $where, array &$result): ?Region
    {
        $this->regions ??= Region::query()->get()->keyBy(fn (Region $region) => $this->letters($region->name));

        $alias = config('manual_allocation.regions')[Str::lower(trim($name))] ?? $name;
        $region = $this->regions[$this->letters($alias)] ?? null;

        if (! $region) {
            $result['errors'][] = "{$where}: region \"{$name}\" is not set up.";

            return null;
        }

        if ($result['region_id'] !== null && $result['region_id'] !== $region->id) {
            $result['errors'][] = "{$where}: the workbook is for {$result['region']}, not {$region->name}.";

            return null;
        }

        $result['region'] = $region->name;
        $result['region_id'] = $region->id;

        return $region;
    }

    private function zone(array $line, string $where, array &$result): ?string
    {
        $code = (string) ($line['zone_code'] ?? '');
        $label = (string) ($line['zone'] ?? '');

        if ($code === '' && $label === '') {
            $result['errors'][] = "{$where}: no zone.";

            return null;
        }

        // A zone without a code is keyed by its name
        $code = $code !== '' ? $code : 'name:' . Str::lower($label);
        $result['zones'][$code] ??= [
            'name' => $label, 'label' => $label, 'code' => str_starts_with($code, 'name:') ? null : $code, 'zone_id' => null,
            'qr_code' => null, 'qr_text' => null, 'delivery_point' => null, 'titles' => 0, 'student' => 0, 'teacher' => 0, 'cartons' => 0,
        ];

        return $code;
    }

    private function seriesOf(string $studentCode, string $teacherCode, string $where, array &$result): ?string
    {
        foreach (array_filter([$studentCode, $teacherCode]) as $code) {
            if (! isset($result['codes'][$code])) {
                $result['errors'][] = "{$where}: book code {$code} is not in 1_Books.";

                return null;
            }
        }

        $series = $result['codes'][$studentCode ?: $teacherCode];
        if ($studentCode && $teacherCode && $result['codes'][$teacherCode] !== $series) {
            $result['errors'][] = "{$where}: {$teacherCode} is not the teacher guide of {$studentCode}.";

            return null;
        }

        return $series;
    }

    // "25110-001-2-1682" is title 25110-001; anything else is its own title
    private function series(string $code): string
    {
        return preg_match('/^(.+)-[12]-\d+$/', trim($code), $match) ? $match[1] : trim($code);
    }

    private function edition(string $written, string $code): string
    {
        if (Str::contains(Str::lower($written), ['teacher', 'guide'])) {
            return ManualBook::EDITION_TEACHER;
        }

        if ($written === '' && preg_match('/-1-\d+$/', $code)) {
            return ManualBook::EDITION_TEACHER;
        }

        return ManualBook::EDITION_STUDENT;
    }

    // "Arsii Aanaa 28" is Arsii; aliases map local names onto zones already set up
    private function zoneName(string $label): string
    {
        $name = trim(preg_replace(config('manual_allocation.printer.zone_suffix_pattern'), '', $label)) ?: $label;

        return config('manual_allocation.zones')[Str::lower($name)] ?? $name;
    }

    /**
     * "MT(Afaan Oromo)" is Mother Tongue in Afaan Oromo, "Second Language(Amharic)" is Amharic
     * 2nd Language, "Moral Education(Gadda)" is Moral Education; the rest go through the aliases.
     *
     * @return array{0: string, 1: ?string} subject and language
     */
    public function subjectAndLanguage(string $written, string $language = ''): array
    {
        $written = trim(preg_replace('/\s+/u', ' ', $written));
        $languageOf = fn (string $value) => config('manual_allocation.languages')[Str::lower(trim($value))] ?? trim($value);
        $language = $language !== '' ? $languageOf($language) : null;

        if (preg_match('/^(.*?)\s*\((.+)\)\s*$/u', $written, $match)) {
            [$base, $variant] = [Str::lower(trim($match[1])), trim($match[2])];

            if (in_array($base, config('manual_allocation.printer.mother_tongue'), true)) {
                return ['Mother Tongue', $language ?? $languageOf($variant)];
            }

            if (in_array($base, config('manual_allocation.printer.second_language'), true)) {
                $second = $languageOf($variant);

                return ["{$second} 2nd Language", $language ?? $second];
            }

            $written = trim($match[1]);
        }

        return [config('manual_allocation.subjects')[Str::lower($written)] ?? $written, $language];
    }

    // "Grade 3" is grade 3, "Level 1" is KG L1
    public function gradeName(string $written): string
    {
        $value = Str::lower(trim($written));

        if (preg_match('/^(?:g|grade)\s*(\d{1,2})$/', $value, $match) || preg_match('/^(\d{1,2})$/', $value, $match)) {
            return (string) (int) $match[1];
        }

        return config('manual_allocation.grades')[$value] ?? trim($written);
    }

    // ---- Saving --------------------------------------------------------------------------

    /**
     * @return array<string, int> zone id per zone code
     */
    private function saveZones(Region $region, array $zones): array
    {
        $existing = Zone::query()->where('region_id', $region->id)->get();
        $byCode = $existing->filter(fn (Zone $zone) => filled($zone->code))->keyBy('code');
        $byName = $existing->keyBy(fn (Zone $zone) => ManualScanService::zoneKey($zone->name));

        $ids = [];
        foreach ($zones as $code => $zone) {
            $found = ($zone['code'] ? $byCode[$zone['code']] ?? null : null)
                ?? $byName[ManualScanService::zoneKey($zone['name'])] ?? null;

            if (! $found) {
                $found = Zone::query()->create([
                    'name' => $zone['name'],
                    'code' => $zone['code'],
                    'region_id' => $region->id,
                    'country_id' => $region->country_id,
                ]);
                $byName[ManualScanService::zoneKey($zone['name'])] = $found;
            } elseif ($zone['code'] && $found->code !== $zone['code']) {
                // The printer's zone code is what the package QR carries
                $found->update(['code' => $zone['code']]);
            }

            $ids[$code] = $found->id;
        }

        return $ids;
    }

    private function saveBook(Region $region, string $series, array $book): ManualBook
    {
        $gradeName = $this->gradeName($book['grade']);
        [$subjectName, $language] = $this->subjectAndLanguage($book['subject'], $book['language']);

        $grade = Grade::query()->firstOrCreate(['name' => $gradeName]);
        $subject = Subject::query()->where('name', $subjectName)->first() ?? Subject::query()->create(['name' => $subjectName]);

        $student = $book['editions'][ManualBook::EDITION_STUDENT] ?? null;
        $teacher = $book['editions'][ManualBook::EDITION_TEACHER] ?? null;

        $model = ManualBook::query()->updateOrCreate(['source_key' => self::SOURCE_PREFIX . $series], [
            'code' => $series,
            'print_code' => ($student ?? $teacher)['code'],
            'title' => Str::limit($book['title'], 250, ''),
            'grade_name' => $book['grade'] ?: null,
            'subject_name' => $subjectName,
            'language' => $language,
            'region_id' => $region->id,
            'grade_id' => $grade->id,
            'subject_id' => $subject->id,
            'has_teacher_guide' => $teacher !== null,
            'students_per_package' => $book['per_carton'] ?: 40,
            'isbn' => $book['isbn'],
            'edition' => $book['print_year'],
            'total_copies' => ($student['copies'] ?? 0) + ($teacher['copies'] ?? 0),
        ]);

        foreach ([ManualBook::EDITION_STUDENT => $student, ManualBook::EDITION_TEACHER => $teacher] as $edition => $copy) {
            if ($copy === null) {
                $model->codes()->where('edition', $edition)->delete();

                continue;
            }

            ManualBookCode::query()->updateOrCreate(
                ['manual_book_id' => $model->id, 'edition' => $edition],
                ['payload' => Str::limit($copy['text'], 250, ''), 'print_code' => $copy['code']]
            );
        }

        return $model;
    }

    private function saveAllocation(ManualAllocationImport $import, Region $region, string $year, array $result, array $zoneIds, array $bookIds): void
    {
        ManualAllocation::query()->where('academic_year', $year)->where('region_id', $region->id)->delete();

        $now = now();
        $rows = [];
        $regionTotals = [];
        $titles = collect($result['books'])->map(fn ($book) => $book['title']);

        foreach ($result['zone_rows'] as $zoneCode => $titlesInZone) {
            foreach ($titlesInZone as $series => $editions) {
                foreach ($editions as $edition => $line) {
                    $regionTotals[$series][$edition] = ($regionTotals[$series][$edition] ?? 0) + $line['quantity'];
                    $rows[] = [$zoneIds[$zoneCode], $series, $edition, $line['quantity'], '2_Zone_Allocation', $line['row']];
                }
            }
        }

        foreach ($regionTotals as $series => $editions) {
            foreach ($editions as $edition => $quantity) {
                $rows[] = [null, $series, $edition, $quantity, '2_Zone_Allocation', null];
            }
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            ManualAllocation::query()->insert(array_map(fn (array $row) => [
                'manual_allocation_import_id' => $import->id,
                'academic_year' => $year,
                'region_id' => $region->id,
                'zone_id' => $row[0],
                'manual_book_id' => $bookIds[$row[1]],
                'edition' => $row[2],
                'quantity' => $row[3],
                'type_confirmed' => true,
                'source_title' => Str::limit($titles[$row[1]], 250, ''),
                'source_sheet' => $row[4],
                'source_row' => $row[5],
                'created_at' => $now,
                'updated_at' => $now,
            ], $chunk));
        }
    }

    private function savePackages(Region $region, string $year, array $packages, array $zones, array $bookIds): void
    {
        $now = now();
        $types = config('manual_allocation.printer.carton_types', []);
        $rows = [];

        foreach ($packages as $package) {
            $zone = $zones[$package['zone_code']];
            $zoneId = $zone['zone_id'];
            $typeKey = Str::lower(trim(preg_replace('/\(.*\)/', '', $package['type'])));
            $short = $types[$typeKey] ?? Str::upper(Str::substr(preg_replace('/[^A-Za-z]/', '', $package['type']), 0, 3));
            $code = sprintf('%s/%s/%s%d+%d', $zone['qr_code'] ?? $zone['code'] ?? $package['zone_code'], $package['series'], $short, $package['student'], $package['teacher']);

            $rows[] = [
                'manual_book_id' => $bookIds[$package['series']],
                'academic_year' => $year,
                'region_id' => $region->id,
                'zone_id' => $zoneId,
                'package_code' => $code,
                'package_qr_code' => $zone['qr_code'],
                'package_qr_text' => $zone['qr_text'],
                'carton_type' => Str::limit($package['type'], 50, ''),
                'no_of_packages' => $package['count'],
                'books_per_package' => $package['student'],
                'teacher_guides_per_package' => $package['teacher'],
                'total_books' => $package['student'] * $package['count'],
                'total_teacher_guides' => $package['teacher'] * $package['count'],
                'current_balance' => $package['student'] * $package['count'],
                'status' => 'Packed',
                'notes' => sprintf('%s × %s for %s.', number_format($package['count']), $package['type'], $zone['name']),
                'source_key' => self::SOURCE_PREFIX . $code,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        $kept = array_column($rows, 'source_key');
        // Counts and contents follow the file; balances and status of packages in use stay
        foreach (array_chunk($rows, 500) as $chunk) {
            ManualBookPackage::query()->upsert($chunk, ['source_key'], [
                'manual_book_id', 'academic_year', 'region_id', 'zone_id', 'package_code', 'package_qr_code', 'package_qr_text',
                'carton_type', 'no_of_packages', 'books_per_package', 'teacher_guides_per_package', 'total_books',
                'total_teacher_guides', 'notes', 'updated_at',
            ]);
        }

        ManualBookPackage::query()
            ->where('region_id', $region->id)
            ->where('source_key', 'like', self::SOURCE_PREFIX . '%')
            ->whereNotIn('source_key', $kept)
            ->whereDoesntHave('distributionLines')
            ->delete();
    }

    /**
     * Titles of the region that came from an import but are no longer allocated (an earlier
     * printer file, or the Ministry workbooks the printer data replaces), unless something refers to them.
     */
    private function removeUnlistedBooks(Region $region, array $keptIds): void
    {
        $orphans = ManualBook::query()
            ->where('region_id', $region->id)
            ->whereNotNull('source_key')
            ->whereNotIn('id', $keptIds)
            ->whereDoesntHave('allocations');

        foreach (self::BOOK_REFERENCES as $table) {
            if (Schema::hasTable($table)) {
                $orphans->whereNotExists(fn ($query) => $query->from($table)->whereColumn("{$table}.manual_book_id", 'manual_books.id'));
            }
        }

        $orphans->delete();
    }

    private function letters(string $value): string
    {
        return Str::lower(preg_replace('/[^\pL]+/u', '', $value));
    }

    private function cellValue($cell): mixed
    {
        $value = $cell instanceof FormulaCell ? $cell->getComputedValue() : $cell->getValue();

        return $value instanceof \DateTimeInterface ? $value->format('Y-m-d') : $value;
    }
}
