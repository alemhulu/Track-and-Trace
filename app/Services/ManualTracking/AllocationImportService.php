<?php

namespace App\Services\ManualTracking;

use App\Models\Grade;
use App\Models\ManualTracking\ManualAllocation;
use App\Models\ManualTracking\ManualAllocationImport;
use App\Models\ManualTracking\ManualBook;
use App\Models\Region;
use App\Models\Subject;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use OpenSpout\Common\Entity\Cell\FormulaCell;
use OpenSpout\Reader\XLSX\Options;
use OpenSpout\Reader\XLSX\Reader;

/**
 * Reads the Ministry's allocation workbooks: a sheet per region with a row per student
 * text (ST) or teacher guide (TG) and its final quantity, an optional Summary (or "All
 * Regions") sheet of totals per region, and an optional split between zones. The split is
 * either a column per zone to the right of Final Quantity on the region sheet, or a sheet
 * per zone: a sheet named after a region is a region sheet; any other sheet is a zone
 * sheet named after its zone. Columns are found by header name because the sheets are
 * laid out differently. The ST and TG rows of a subject, grade and language become one
 * title with two allocated editions; the title keeps the workbook's own book title,
 * language, subject and grade as written (the student text's, when both are listed), and
 * the tidied spellings are only used to pair rows. Nothing is saved while any row has an error.
 */
class AllocationImportService
{
    private const HEADERS = [
        'region' => 'region',
        'language' => 'language',
        'type' => 'type',
        'subject' => 'subject',
        'grade' => 'grade',
        'final quantity' => 'quantity',
        'n sn code' => 'print_code',
    ];

    /** @var array<string, Region> */
    private array $regions = [];

    /** @var array<string, Zone> keyed "region id:lower zone name" */
    private array $zoneIndex = [];

    /** @var array<string, array<string, string>> code printed after a zone column's name, per region and zone */
    private array $zoneCodes = [];

    private ?string $year = null;

    /** Tables whose rows point at a manual book; an imported book is only removed when none does */
    private const BOOK_REFERENCES = [
        'manual_book_codes', 'manual_book_packages', 'manual_distribution_lines', 'manual_scans',
        'manual_stock_balances', 'manual_stock_ledgers', 'student_book_issues', 'teacher_guide_issues',
        'verification_visit_items',
    ];

    /**
     * @return array{
     *     titles: array<string, array>,
     *     regions: array<string, array{lines: int, titles: int, student: int, teacher: int, unconfirmed: int, expected_lines: ?int, expected_total: ?int}>,
     *     awaiting: array<string, array{lines: int, total: int}>,
     *     zones: array<string, array<string, array{zone_id: ?int, lines: int, titles: int, student: int, teacher: int}>>,
     *     zone_rows: array<string, array<string, array<string, array>>>,
     *     new_zones: array<int, string>,
     *     line_count: int,
     *     total: int,
     *     warnings: array<int, string>,
     *     errors: array<int, string>
     * }
     */
    public function parse(string $path, ?string $year = null): array
    {
        $this->regions = Region::query()->get()->keyBy(fn (Region $region) => Str::lower($region->name))->all();
        $this->zoneIndex = Zone::query()->get()->keyBy(fn (Zone $zone) => $zone->region_id . ':' . Str::lower($zone->name))->all();
        $this->zoneCodes = [];
        $this->year = $year;

        $titles = [];
        $regions = [];
        $zoneRows = [];
        $summary = [];
        $warnings = [];
        $errors = [];
        $lineCount = 0;
        $total = 0;

        $options = new Options();
        $options->SHOULD_PRESERVE_EMPTY_ROWS = true;
        $reader = new Reader($options);
        $reader->open($path);

        foreach ($reader->getSheetIterator() as $sheet) {
            $sheetName = trim($sheet->getName());

            if (in_array(Str::lower($sheetName), ['summary', 'all regions'], true)) {
                $summary = $this->readSummary($sheet->getRowIterator());

                continue;
            }

            $isRegionSheet = isset($this->regions[Str::lower($this->clean($sheetName, 'regions'))]);
            $caption = null;
            $columns = null;

            foreach ($sheet->getRowIterator() as $rowNumber => $row) {
                $values = array_map(fn ($cell) => $this->cellValue($cell), $row->getCells());

                if ($columns === null) {
                    $columns = $this->headerColumns($values);
                    $caption ??= collect($values)->map(fn ($value) => trim((string) $value))->first(fn ($value) => $value !== '');

                    continue;
                }

                $line = $this->readLine($values, $columns);
                if ($line === null) {
                    continue;
                }

                $where = "{$sheetName} row {$rowNumber}";

                if (! is_numeric($line['quantity'])) {
                    $errors[] = "{$where}: quantity \"{$line['quantity']}\" is not a number.";

                    continue;
                }

                $quantity = (int) round((float) $line['quantity']);
                if ($quantity <= 0) {
                    $warnings[] = "{$where}: quantity is zero, row skipped.";

                    continue;
                }

                $regionName = $this->clean($line['region'] !== '' ? $line['region'] : $sheetName, 'regions');
                $region = $this->regions[Str::lower($regionName)] ?? null;
                if (! $region) {
                    $errors[] = "{$where}: region \"{$regionName}\" is not set up (run the region seeder or add an alias).";

                    continue;
                }

                $language = preg_replace(['/\s+fil$/i', config('manual_allocation.language_suffix_pattern', '/$^/')], '', trim($line['language']));
                $language = $this->clean($language, 'languages');
                $subject = $this->clean($line['subject'], 'subjects');
                if (in_array(Str::lower($subject), config('manual_allocation.language_subjects', []), true)) {
                    $subject = $language;
                }

                $subject = $subject !== '' ? $subject : $this->subjectFromTitle($line['title'], $language);
                if ($subject === '') {
                    $errors[] = "{$where}: subject is empty and the title \"{$line['title']}\" does not name one.";

                    continue;
                }

                [$edition, $confirmed] = $this->edition($line['type'], $line['title']);

                $variant = preg_match(config('manual_allocation.second_language_pattern'), $line['title'].' '.$line['subject']) ? '2nd' : '';
                $grade = $line['grade'] !== '' ? $this->grade($line['grade']) : $this->previousGrade($region, $language, $subject, $variant, $edition, $quantity);
                if ($grade === null) {
                    $errors[] = $line['grade'] !== ''
                        ? "{$where}: unknown grade \"{$line['grade']}\"."
                        : "{$where}: grade is empty and no earlier allocation of \"{$line['title']}\" settles it.";

                    continue;
                }

                $key = Str::lower(implode('|', [$region->name, $language, $subject, $grade, $variant]));

                $titles[$key] ??= [
                    'region_id' => $region->id,
                    'region' => $region->name,
                    'language' => $language !== '' ? $language : null,
                    'subject' => $subject,
                    'grade' => $grade,
                    'variant' => $variant,
                    'print_code' => null,
                    'editions' => [],
                    'written' => null,
                ];

                // The book is shown as the workbook writes it, preferring the student text's row
                if ($titles[$key]['written'] === null || ($edition === ManualBook::EDITION_STUDENT && $titles[$key]['written']['edition'] !== ManualBook::EDITION_STUDENT)) {
                    $titles[$key]['written'] = [
                        'edition' => $edition,
                        'title' => $line['title'],
                        'language' => $line['language'],
                        'subject' => $line['subject'],
                        'grade' => $line['grade'],
                    ];
                }

                if ($line['print_code'] !== '' && $titles[$key]['print_code'] === null) {
                    $titles[$key]['print_code'] = $line['print_code'];
                }

                $source = ['quantity' => $quantity, 'confirmed' => $confirmed, 'title' => $line['title'], 'sheet' => $sheetName, 'row' => $rowNumber];

                if (! $isRegionSheet) {
                    $this->addZoneRow($zoneRows, $warnings, $region->name, $this->zoneName($region, $sheetName, $caption), $key, $edition, $source, $where);

                    continue;
                }

                foreach ($columns['zones'] ?? [] as $index => $header) {
                    $zoneQuantity = $values[$index] ?? null;
                    if (! is_numeric($zoneQuantity) || (int) round((float) $zoneQuantity) <= 0) {
                        continue;
                    }

                    [$zone, $code] = $this->zoneColumn($header);
                    if (Str::lower($this->clean($zone, 'regions')) === Str::lower($region->name)) {
                        // A single column named after the region (Dire Dawa, Harari) is not a zone split
                        continue;
                    }

                    $zone = $this->zoneName($region, $zone, null);
                    if ($code !== null) {
                        $this->zoneCodes[$region->name][$zone] ??= $code;
                    }

                    $this->addZoneRow($zoneRows, $warnings, $region->name, $zone, $key, $edition, array_merge($source, ['quantity' => (int) round((float) $zoneQuantity)]), $where);
                }

                if (isset($titles[$key]['editions'][$edition])) {
                    $first = $titles[$key]['editions'][$edition];
                    $warnings[] = "{$where}: \"{$line['title']}\" repeats {$first['sheet']} row {$first['row']} ({$first['title']}); quantities added together.";
                    $titles[$key]['editions'][$edition]['quantity'] += $quantity;
                    $titles[$key]['editions'][$edition]['confirmed'] = $first['confirmed'] && $confirmed;
                } else {
                    $titles[$key]['editions'][$edition] = $source;
                }

                $regions[$region->name] ??= ['lines' => 0, 'titles' => 0, 'student' => 0, 'teacher' => 0, 'unconfirmed' => 0, 'expected_lines' => null, 'expected_total' => null];
                $regions[$region->name]['lines']++;
                $regions[$region->name][$edition === ManualBook::EDITION_TEACHER ? 'teacher' : 'student'] += $quantity;
                $regions[$region->name]['unconfirmed'] += $confirmed ? 0 : 1;
                $lineCount++;
                $total += $quantity;
            }
        }

        $reader->close();

        [$zones, $newZones] = $this->reconcileZones($titles, $regions, $zoneRows, $warnings);
        $lineCount = array_sum(array_column($regions, 'lines'));
        $total = array_sum(array_column($regions, 'student')) + array_sum(array_column($regions, 'teacher'));

        foreach ($titles as $title) {
            if ($title['editions']) {
                $regions[$title['region']]['titles']++;
            }
        }

        $summary += $year !== null ? $this->earlierSummary($year, array_keys($regions), array_keys($summary)) : [];

        $awaiting = [];
        foreach ($summary as $name => $expected) {
            if (! isset($regions[$name])) {
                $awaiting[$name] = $expected;
                $warnings[] = "{$name}: listed in the Summary ({$expected['lines']} lines, " . number_format($expected['total']) . ' copies) but has no sheet; awaiting data.';

                continue;
            }

            $regions[$name]['expected_lines'] = $expected['lines'];
            $regions[$name]['expected_total'] = $expected['total'];
            $read = $regions[$name]['student'] + $regions[$name]['teacher'];

            if ($read !== $expected['total'] || $regions[$name]['lines'] !== $expected['lines']) {
                $warnings[] = "{$name}: read {$regions[$name]['lines']} lines and " . number_format($read) . " copies; the Summary says {$expected['lines']} and " . number_format($expected['total']) . '.';
            }
        }

        $unconfirmed = array_sum(array_column($regions, 'unconfirmed'));
        if ($unconfirmed > 0) {
            $warnings[] = "{$unconfirmed} rows had no clear type (TBD) and were taken as student texts; check them on the Allocations page.";
        }

        ksort($regions);

        return [
            'titles' => $titles,
            'regions' => $regions,
            'awaiting' => $awaiting,
            'zones' => $zones,
            'zone_rows' => $zoneRows,
            'new_zones' => $newZones,
            'line_count' => $lineCount,
            'total' => $total,
            'warnings' => $warnings,
            'errors' => $errors,
        ];
    }

    /**
     * Saves the workbook's titles and quantities for the year. Only what the workbook covers
     * is replaced: the region allocation of each region it has, and the allocation of each
     * zone it has a sheet for. Missing zones are created under their region.
     */
    public function commit(?User $actor, string $path, string $fileName, string $year): array
    {
        $result = $this->parse($path, $year);

        if ($result['errors']) {
            return $result + ['import' => null];
        }

        $result['import'] = DB::transaction(function () use ($actor, $result, $fileName, $year): ManualAllocationImport {
            $import = ManualAllocationImport::create([
                'file_name' => $fileName,
                'academic_year' => $year,
                'imported_by' => $actor?->id,
                'line_count' => $result['line_count'],
                'title_count' => count($result['titles']),
                'total_quantity' => $result['total'],
                'region_totals' => ['regions' => $result['regions'], 'awaiting' => $result['awaiting'], 'zones' => $result['zones']],
                'warnings' => $result['warnings'],
            ]);

            $zoneIds = [];
            foreach ($result['zone_rows'] as $regionName => $regionZones) {
                $region = $this->regions[Str::lower($regionName)];
                foreach (array_keys($regionZones) as $zoneName) {
                    $zoneIds[$regionName][$zoneName] = Zone::firstOrCreate(
                        ['name' => $zoneName, 'region_id' => $region->id],
                        ['country_id' => $region->country_id, 'code' => $this->zoneCodes[$regionName][$zoneName] ?? null]
                    )->id;
                }
            }

            $regionIds = [];
            $books = [];
            $kept = [];
            $save = function (int $regionId, ?int $zoneId, int $bookId, string $edition, array $row) use ($year, $import, &$kept): void {
                $kept[] = ManualAllocation::updateOrCreate(
                    ['academic_year' => $year, 'region_id' => $regionId, 'zone_id' => $zoneId, 'manual_book_id' => $bookId, 'edition' => $edition],
                    [
                        'manual_allocation_import_id' => $import->id,
                        'quantity' => $row['quantity'],
                        'type_confirmed' => $row['confirmed'],
                        'source_title' => Str::limit($row['title'], 250, ''),
                        'source_sheet' => $row['sheet'],
                        'source_row' => $row['row'],
                    ]
                )->id;
            };

            foreach ($result['titles'] as $key => $title) {
                $books[$key] = $this->saveBook($key, $title)->id;

                foreach ($title['editions'] as $edition => $row) {
                    $save($title['region_id'], null, $books[$key], $edition, $row);
                    $regionIds[$title['region_id']] = true;
                }
            }

            foreach ($result['zone_rows'] as $regionName => $regionZones) {
                foreach ($regionZones as $zoneName => $rows) {
                    foreach ($rows as $key => $editions) {
                        foreach ($editions as $edition => $row) {
                            $save($result['titles'][$key]['region_id'], $zoneIds[$regionName][$zoneName], $books[$key], $edition, $row);
                        }
                    }
                }
            }

            ManualAllocation::query()
                ->where('academic_year', $year)
                ->whereNull('zone_id')
                ->whereIn('region_id', array_keys($regionIds))
                ->whereNotIn('id', $kept)
                ->delete();

            ManualAllocation::query()
                ->where('academic_year', $year)
                ->whereIn('zone_id', collect($zoneIds)->flatten()->all())
                ->whereNotIn('id', $kept)
                ->delete();

            $this->removeOrphanedBooks();

            return $import;
        });

        return $result;
    }

    /**
     * Sets each region's zone split against its region sheet. A region with zone sheets but
     * no region sheet gets the zone totals as its allocation.
     *
     * @return array{0: array, 1: array<int, string>} zone totals and the zones still to create
     */
    private function reconcileZones(array &$titles, array &$regions, array $zoneRows, array &$warnings): array
    {
        $zones = [];
        $newZones = [];

        foreach ($zoneRows as $regionName => $regionZones) {
            $region = $this->regions[Str::lower($regionName)];
            $sums = [];

            foreach ($regionZones as $zoneName => $rows) {
                $known = $this->zoneIndex[$region->id . ':' . Str::lower($zoneName)] ?? null;
                $zones[$regionName][$zoneName] = ['zone_id' => $known?->id, 'lines' => 0, 'titles' => count($rows), 'student' => 0, 'teacher' => 0];
                if (! $known) {
                    $newZones[] = "{$regionName} / {$zoneName}";
                }

                foreach ($rows as $key => $editions) {
                    foreach ($editions as $edition => $row) {
                        $zones[$regionName][$zoneName]['lines']++;
                        $zones[$regionName][$zoneName][$edition === ManualBook::EDITION_TEACHER ? 'teacher' : 'student'] += $row['quantity'];
                        $sums[$key][$edition] ??= array_merge($row, ['quantity' => 0, 'confirmed' => true]);
                        $sums[$key][$edition]['quantity'] += $row['quantity'];
                        $sums[$key][$edition]['confirmed'] = $sums[$key][$edition]['confirmed'] && $row['confirmed'];
                    }
                }
            }

            if (! isset($regions[$regionName])) {
                $warnings[] = "{$regionName}: no region sheet; the region allocation is the sum of its zone sheets.";
                $regions[$regionName] = ['lines' => 0, 'titles' => 0, 'student' => 0, 'teacher' => 0, 'unconfirmed' => 0, 'expected_lines' => null, 'expected_total' => null];

                foreach ($sums as $key => $editions) {
                    foreach ($editions as $edition => $row) {
                        $titles[$key]['editions'][$edition] = $row;
                        $regions[$regionName]['lines']++;
                        $regions[$regionName][$edition === ManualBook::EDITION_TEACHER ? 'teacher' : 'student'] += $row['quantity'];
                        $regions[$regionName]['unconfirmed'] += $row['confirmed'] ? 0 : 1;
                    }
                }

                continue;
            }

            $mismatches = 0;
            foreach ($titles as $key => $title) {
                if ($title['region'] !== $regionName) {
                    continue;
                }

                foreach (array_unique([...array_keys($title['editions']), ...array_keys($sums[$key] ?? [])]) as $edition) {
                    $regionQuantity = $title['editions'][$edition]['quantity'] ?? 0;
                    $zoneQuantity = $sums[$key][$edition]['quantity'] ?? 0;

                    if ($regionQuantity !== $zoneQuantity && $mismatches++ < 20) {
                        $label = $title['editions'][$edition]['title'] ?? $sums[$key][$edition]['title'];
                        $warnings[] = "{$regionName}: \"{$label}\" ({$edition}) is " . number_format($regionQuantity) . ' on the region sheet but ' . number_format($zoneQuantity) . ' across the zone sheets.';
                    }
                }
            }

            if ($mismatches > 20) {
                $warnings[] = "{$regionName}: " . ($mismatches - 20) . ' more titles differ between the region sheet and the zone sheets.';
            }
        }

        return [$zones, $newZones];
    }

    /**
     * Totals an earlier import of the year listed as awaiting, for regions this workbook
     * brings but whose own Summary does not cover them.
     *
     * @return array<string, array{lines: int, total: int}>
     */
    private function earlierSummary(string $year, array $regionNames, array $covered): array
    {
        $expected = [];

        ManualAllocationImport::query()->where('academic_year', $year)->orderBy('id')->get(['region_totals'])
            ->each(function (ManualAllocationImport $import) use (&$expected, $regionNames, $covered): void {
                foreach ($import->region_totals['awaiting'] ?? [] as $name => $totals) {
                    if (in_array($name, $regionNames, true) && ! in_array($name, $covered, true)) {
                        $expected[$name] = $totals;
                    }
                }
            });

        return $expected;
    }

    /**
     * Adds a zone's quantity of a title, summing a title the zone lists twice.
     */
    private function addZoneRow(array &$zoneRows, array &$warnings, string $regionName, string $zone, string $key, string $edition, array $source, string $where): void
    {
        $existing = $zoneRows[$regionName][$zone][$key][$edition] ?? null;

        if ($existing) {
            $warnings[] = "{$where}: \"{$source['title']}\" repeats {$existing['sheet']} row {$existing['row']} for {$zone}; quantities added together.";
            $source = array_merge($existing, [
                'quantity' => $existing['quantity'] + $source['quantity'],
                'confirmed' => $existing['confirmed'] && $source['confirmed'],
            ]);
        }

        $zoneRows[$regionName][$zone][$key][$edition] = $source;
    }

    /**
     * Splits a zone column header such as "Arsii (1001)" or "Gamo zone (0204)" into a name and its code.
     *
     * @return array{0: string, 1: ?string}
     */
    private function zoneColumn(string $header): array
    {
        $header = trim(preg_replace('/\s+/', ' ', $header));
        $code = preg_match('/\s*\((\d+)\)$/', $header, $match) ? $match[1] : null;
        $name = preg_replace(['/\s*\(\d+\)$/', '/\bspecal\b/i', '/\s+zone$/i'], ['', 'Special', ' Zone'], $header);

        return [$name, $code];
    }

    /**
     * Names the subject of a row whose subject cell is empty, from words in its title, or
     * the row's language when the title starts with it ("Amharic Grade 3 Student Book").
     */
    private function subjectFromTitle(string $title, string $language): string
    {
        foreach (config('manual_allocation.subject_title_patterns', []) as $pattern => $subject) {
            if (preg_match($pattern, $title)) {
                return $subject;
            }
        }

        return $language !== '' && Str::startsWith(Str::lower($title), Str::lower($language)) ? $language : '';
    }

    /**
     * The grade of the year's one region allocation with the same language, subject, edition
     * and quantity, for a row whose grade cell is empty.
     */
    private function previousGrade(Region $region, string $language, string $subject, string $variant, string $edition, int $quantity): ?string
    {
        $like = fn (string $part) => addcslashes(Str::lower($part), '\\%_');

        $grades = ManualAllocation::query()
            ->join('manual_books', 'manual_books.id', '=', 'manual_allocations.manual_book_id')
            ->join('grades', 'grades.id', '=', 'manual_books.grade_id')
            ->where('manual_allocations.academic_year', $this->year ?? ManualAllocation::latestYear())
            ->where('manual_allocations.region_id', $region->id)
            ->whereNull('manual_allocations.zone_id')
            ->where('manual_allocations.edition', $edition)
            ->where('manual_allocations.quantity', $quantity)
            ->where('manual_books.source_key', 'like', $like("{$region->name}|{$language}|{$subject}|") . '%|' . $like($variant))
            ->pluck('grades.name');

        return $grades->count() === 1 ? $grades->first() : null;
    }

    /**
     * Removes imported titles left without an allocation that nothing else points at, such
     * as titles a corrected workbook now spells differently.
     */
    private function removeOrphanedBooks(): void
    {
        $orphans = ManualBook::query()->whereNotNull('source_key')->whereDoesntHave('allocations');

        foreach (self::BOOK_REFERENCES as $table) {
            if (DB::getSchemaBuilder()->hasTable($table)) {
                $orphans->whereNotExists(fn ($query) => $query->from($table)->whereColumn("{$table}.manual_book_id", 'manual_books.id'));
            }
        }

        $orphans->delete();
    }

    private function zoneName(Region $region, string $sheetName, ?string $caption): string
    {
        // "East Shewa" matches a zone registered as "East Shewa Zone", and the other way round
        foreach ([$sheetName, (string) $caption] as $candidate) {
            $name = $this->clean($candidate, 'zones');

            foreach ([$name, "{$name} Zone", preg_replace('/\s+zone$/i', '', $name)] as $spelling) {
                if (isset($this->zoneIndex[$region->id . ':' . Str::lower($spelling)])) {
                    return $this->zoneIndex[$region->id . ':' . Str::lower($spelling)]->name;
                }
            }
        }

        return $this->clean($sheetName, 'zones');
    }

    private function saveBook(string $key, array $title): ManualBook
    {
        $grade = Grade::firstOrCreate(['name' => $title['grade']]);
        $subject = Subject::query()->where('name', $title['subject'])->first()
            ?? Subject::create(['name' => $title['subject']]);

        $student = $title['editions'][ManualBook::EDITION_STUDENT]['quantity'] ?? 0;
        $teacher = $title['editions'][ManualBook::EDITION_TEACHER]['quantity'] ?? 0;

        $written = $title['written'];

        return ManualBook::updateOrCreate(['source_key' => $key], [
            'title' => Str::limit($written['title'], 250, ''),
            'grade_name' => $written['grade'] !== '' ? $written['grade'] : null,
            'subject_name' => $written['subject'] !== '' ? $written['subject'] : null,
            'language' => $written['language'] !== '' ? $written['language'] : null,
            'region_id' => $title['region_id'],
            'grade_id' => $grade->id,
            'subject_id' => $subject->id,
            'has_teacher_guide' => $teacher > 0,
            'print_code' => $title['print_code'],
            'total_copies' => $student + $teacher,
        ]);
    }

    /**
     * @param  array<int, mixed>  $values
     * @return array<string, int>|null column index per field, or null when this is not the header row
     */
    private function headerColumns(array $values): ?array
    {
        $columns = [];

        foreach ($values as $index => $value) {
            $header = Str::lower(preg_replace('/\s+/', ' ', trim((string) $value)));

            if (isset(self::HEADERS[$header])) {
                $columns[self::HEADERS[$header]] ??= $index;
            } elseif (Str::startsWith($header, 'book title')) {
                $columns['title'] ??= $index;
            } elseif ($header !== '' && isset($columns['quantity']) && ! in_array($header, config('manual_allocation.ignored_columns', []), true)) {
                // Any other column right of Final Quantity holds a zone's share
                $columns['zones'][$index] = trim((string) $value);
            }
        }

        return isset($columns['quantity'], $columns['title']) ? $columns : null;
    }

    /**
     * @return array<string, string>|null null for blank, total and caption rows
     */
    private function readLine(array $values, ?array $columns): ?array
    {
        if ($columns === null) {
            return null;
        }

        $line = [];
        foreach (['region', 'language', 'type', 'subject', 'grade', 'title', 'quantity', 'print_code'] as $field) {
            $line[$field] = isset($columns[$field]) ? trim(preg_replace('/\s+/', ' ', (string) ($values[$columns[$field]] ?? ''))) : '';
        }

        if ($line['title'] === '' || $line['quantity'] === '' || preg_match('/total|distribution/i', $line['region']) || preg_match('/^(grand )?total$/i', $line['title'])) {
            return null;
        }

        return $line;
    }

    /**
     * @return array<string, array{lines: int, total: int}>
     */
    private function readSummary(iterable $rows): array
    {
        $summary = [];
        $columns = null;

        foreach ($rows as $row) {
            $values = array_map(fn ($cell) => Str::lower(trim((string) $this->cellValue($cell))), $row->getCells());

            if ($columns === null) {
                $name = collect(['region name', 'region'])->map(fn ($header) => array_search($header, $values, true))->first(fn ($index) => $index !== false);
                if ($name !== null) {
                    $lines = collect($values)->search(fn ($value) => in_array($value, ['number of books', 'number of lines', 'number of titles'], true))
                        ?: collect($values)->search(fn ($value) => Str::startsWith($value, 'number of'));
                    $total = collect($values)->search(fn ($value) => Str::startsWith($value, 'total'));
                    $columns = compact('name', 'lines', 'total');
                }

                continue;
            }

            $name = $values[$columns['name']] ?? '';
            if ($name === '' || Str::contains($name, 'total')) {
                continue;
            }

            $region = $this->clean($name, 'regions');
            $region = ($this->regions[Str::lower($region)] ?? null)?->name ?? Str::title($region);
            $summary[$region] = [
                'lines' => (int) ($values[$columns['lines']] ?? 0),
                'total' => (int) round((float) ($values[$columns['total']] ?? 0)),
            ];
        }

        return $summary;
    }

    private function cellValue($cell): mixed
    {
        $value = $cell instanceof FormulaCell ? $cell->getComputedValue() : $cell->getValue();

        return $value instanceof \DateTimeInterface ? $value->format('Y-m-d') : $value;
    }

    /**
     * Collapses spaces, applies the configured alias, and tidies all-capital or all-lower words.
     */
    private function clean(?string $value, string $aliases): string
    {
        $value = trim(preg_replace('/\s+/', ' ', (string) $value));
        $alias = config("manual_allocation.{$aliases}")[Str::lower($value)] ?? null;

        if ($alias !== null) {
            return $alias;
        }

        return ($value === Str::upper($value) || $value === Str::lower($value)) && strlen($value) > 3
            ? Str::title(Str::lower($value))
            : $value;
    }

    private function grade(string $value): ?string
    {
        $value = Str::lower(trim($value));

        if (preg_match('/^(?:g|grade)\s*(\d{1,2})$/', $value, $match)) {
            return (string) (int) $match[1];
        }

        if (preg_match('/^\d{1,2}$/', $value)) {
            return (string) (int) $value;
        }

        return config('manual_allocation.grades')[$value] ?? null;
    }

    /**
     * @return array{0: string, 1: bool} edition and whether the sheet stated it
     */
    private function edition(string $type, string $title): array
    {
        $type = Str::upper(trim($type));

        if ($type === 'ST') {
            return [ManualBook::EDITION_STUDENT, true];
        }

        if ($type === 'TG') {
            return [ManualBook::EDITION_TEACHER, true];
        }

        $title = Str::lower($title);
        $matches = fn (array $words) => collect($words)->contains(
            fn ($word) => preg_match('/(?<![a-z])' . preg_quote($word, '/') . '(?![a-z])/', $title)
        );

        if ($matches(config('manual_allocation.teacher_keywords', []))) {
            return [ManualBook::EDITION_TEACHER, true];
        }

        if ($matches(config('manual_allocation.student_keywords', []))) {
            return [ManualBook::EDITION_STUDENT, true];
        }

        return [ManualBook::EDITION_STUDENT, false];
    }
}
