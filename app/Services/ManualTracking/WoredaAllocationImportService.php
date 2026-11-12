<?php

namespace App\Services\ManualTracking;

use App\Models\ManualTracking\ManualAllocation;
use App\Models\ManualTracking\ManualAllocationImport;
use App\Models\ManualTracking\ManualBook;
use App\Models\ManualTracking\ManualWoredaAllocation;
use App\Models\Region;
use App\Models\User;
use App\Models\Woreda;
use App\Models\Zone;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use OpenSpout\Common\Entity\Cell\FormulaCell;
use OpenSpout\Reader\XLSX\Options;
use OpenSpout\Reader\XLSX\Reader;

/**
 * Reads a regional education bureau's woreda split of its allocation: a sheet per subject
 * with the zone and woreda in the first two columns, a grade header row, and under it a
 * student text (STB / Practs Book) and teacher guide (TG) column per grade. The zone is only
 * written on its first woreda, subtotal rows ("Idaáma") and the grand total are skipped,
 * and total columns are ignored.
 *
 * Each sheet is matched to the region's already allocated titles by subject and grade
 * (config manual_allocation.woreda_sheets); no title is created. The bureau's quantities are
 * fractional shares, so they are rounded per zone, title and edition with the largest
 * remainder method onto the zone's own allocation, keeping woreda shares equal to it.
 */
class WoredaAllocationImportService
{
    private Region $region;

    /** @var array<string, Zone> keyed by lower-case name */
    private array $zoneIndex = [];

    /** @var array<string, array<int, Woreda>> existing woredas keyed "zone id" => [lower name => woreda] */
    private array $woredaIndex = [];

    /**
     * @return array{
     *     region: ?string, region_id: ?int,
     *     sheets: array<string, array{subject: ?string, rows: int, titles: array<int, string>, missing: array<int, string>}>,
     *     zones: array<string, array{zone_id: int, woredas: int, new_woredas: int, student: int, teacher: int, zone_student: ?int, zone_teacher: ?int}>,
     *     rows: array<int, array>,
     *     woreda_count: int, new_woredas: int, line_count: int, title_count: int, total: int,
     *     warnings: array<int, string>, errors: array<int, string>
     * }
     */
    public function parse(string $path, string $regionName, string $year): array
    {
        $result = [
            'region' => null, 'region_id' => null, 'sheets' => [], 'zones' => [], 'rows' => [],
            'woreda_count' => 0, 'new_woredas' => 0, 'line_count' => 0, 'title_count' => 0, 'total' => 0,
            'warnings' => [], 'errors' => [],
        ];

        $region = Region::query()->where('name', trim($regionName))->first()
            ?? Region::query()->where('name', 'like', '%' . trim($regionName) . '%')->first();
        if (! $region) {
            $result['errors'][] = "Region \"{$regionName}\" is not set up.";

            return $result;
        }

        $this->region = $region;
        $result['region'] = $region->name;
        $result['region_id'] = $region->id;
        $this->indexPlaces();

        $books = $this->allocatedBooks($year);
        if ($books->isEmpty()) {
            $result['errors'][] = "{$region->name} has no allocation for " . $year . '; import the Ministry allocation first.';

            return $result;
        }

        $sheets = $this->readSheets($path);

        // The zones the allocation covers. A sheet whose zone column lists towns ("B/M/Asallaa") can
        // match a zone named after the town that the allocation does not cover; such a row goes to
        // the zone the other sheets put its woreda in
        $allocatedZones = ManualAllocation::query()
            ->where('academic_year', $year)->where('region_id', $region->id)->whereNotNull('zone_id')
            ->distinct()->pluck('zone_id')->flip();
        $covered = fn (?Zone $zone) => $zone && isset($allocatedZones[$zone->id]);

        // Woreda → zone from the sheets that name an allocated zone
        $woredaZones = [];
        foreach ($sheets as $sheet) {
            foreach ($sheet['lines'] as $line) {
                if ($covered($line['zone']) && ! isset($woredaZones[$line['woreda_key']])) {
                    $woredaZones[$line['woreda_key']] = $line['zone'];
                }
            }
        }

        // shares[zone id][book id][edition][woreda key] = fractional quantity
        $shares = [];
        $sources = [];
        $woredaNames = [];
        $titles = [];

        foreach ($sheets as $sheetName => $sheet) {
            $subjects = $this->sheetSubjects($sheetName . ' ' . $sheet['caption']);
            $entry = ['subject' => $subjects ? $subjects[0] : null, 'rows' => 0, 'titles' => [], 'missing' => []];

            if (! $sheet['lines'] && ! $sheet['columns']) {
                continue;
            }
            if (! $subjects) {
                $result['warnings'][] = "Sheet \"{$sheetName}\": no subject matches its name; skipped. Add a keyword to manual_allocation.woreda_sheets.";
                $result['sheets'][$sheetName] = $entry;

                continue;
            }
            if (! $sheet['columns']) {
                $result['warnings'][] = "Sheet \"{$sheetName}\": no grade and STB/TG header rows found; skipped.";
                $result['sheets'][$sheetName] = $entry;

                continue;
            }

            // grade => book, for the grades this sheet has columns for
            $gradeBooks = [];
            foreach (collect($sheet['columns'])->keyBy('grade') as $grade => $column) {
                $book = $books->first(fn (ManualBook $book) => in_array(Str::lower((string) $book->subject_name), array_map('strtolower', $subjects), true)
                    && array_intersect($this->gradeKeys((string) $book->grade_name), $column['keys']));

                if ($book) {
                    $gradeBooks[$grade] = $book;
                    $entry['titles'][] = $book->title;
                    $titles[$book->id] = true;
                } else {
                    $entry['missing'][] = "Grade {$grade}";
                    $result['warnings'][] = "Sheet \"{$sheetName}\": {$region->name} has no allocated {$subjects[0]} title for grade {$grade}; its columns are skipped.";
                }
            }

            foreach ($sheet['lines'] as $line) {
                $zone = $covered($line['zone'])
                    ? $line['zone']
                    : ($woredaZones[$line['woreda_key']] ?? $line['zone']);
                if (! $zone) {
                    $result['warnings'][] = "Sheet \"{$sheetName}\" row {$line['row']}: zone \"{$line['zone_label']}\" of woreda \"{$line['woreda']}\" is not a zone of {$region->name}; row skipped.";

                    continue;
                }

                $entry['rows']++;
                $woredaNames[$zone->id][$line['woreda_key']] ??= $line['woreda'];

                foreach ($sheet['columns'] as $column) {
                    $book = $gradeBooks[$column['grade']] ?? null;
                    $value = $line['values'][$column['index']] ?? null;
                    if (! $book || ! is_numeric($value) || (float) $value < 0) {
                        continue;
                    }

                    $current = $shares[$zone->id][$book->id][$column['edition']][$line['woreda_key']] ?? null;
                    if ($current !== null && ! isset($sources[$zone->id][$book->id][$column['edition']][$line['woreda_key']]['warned'])) {
                        $result['warnings'][] = "Sheet \"{$sheetName}\" row {$line['row']}: woreda \"{$line['woreda']}\" is listed twice under {$zone->name}; the quantities are added.";
                        $sources[$zone->id][$book->id][$column['edition']][$line['woreda_key']]['warned'] = true;
                    }

                    $shares[$zone->id][$book->id][$column['edition']][$line['woreda_key']] = ($current ?? 0) + (float) $value;
                    $sources[$zone->id][$book->id][$column['edition']][$line['woreda_key']]['sheet'] ??= Str::limit($sheetName, 100, '');
                    $sources[$zone->id][$book->id][$column['edition']][$line['woreda_key']]['row'] ??= $line['row'];
                }
            }

            $result['sheets'][$sheetName] = $entry;
        }

        // The zones' own allocation, which the rounded woreda shares must add up to
        $zoneTotals = ManualAllocation::query()
            ->where('academic_year', $year)->where('region_id', $region->id)->whereNotNull('zone_id')
            ->whereIn('zone_id', array_keys($shares))
            ->get(['zone_id', 'manual_book_id', 'edition', 'quantity'])
            ->keyBy(fn ($row) => "{$row->zone_id}:{$row->manual_book_id}:{$row->edition}");

        $zonesById = collect($this->zoneIndex)->keyBy('id');
        $bookTitles = $books->pluck('title', 'id');

        foreach ($shares as $zoneId => $zoneBooks) {
            $zone = $zonesById[$zoneId];
            $summary = ['zone_id' => $zoneId, 'woredas' => count($woredaNames[$zoneId] ?? []), 'new_woredas' => 0,
                'student' => 0, 'teacher' => 0, 'zone_student' => null, 'zone_teacher' => null];

            foreach ($woredaNames[$zoneId] ?? [] as $key => $name) {
                if (! isset($this->woredaIndex[$zoneId][$key])) {
                    $summary['new_woredas']++;
                }
            }

            foreach ($zoneBooks as $bookId => $editions) {
                foreach ($editions as $edition => $values) {
                    $sum = array_sum($values);
                    $stored = $zoneTotals["{$zoneId}:{$bookId}:{$edition}"]->quantity ?? null;
                    $target = $stored ?? (int) round($sum);

                    // The bureau's shares drift a copy or two from the zone total through its own rounding
                    if ($stored !== null && abs($sum - $stored) > max(2, $stored * 0.005)) {
                        $result['warnings'][] = sprintf('%s · %s (%s): the woredas add up to %s but the zone is allocated %s; shares are scaled to the zone allocation.',
                            $zone->name, $bookTitles[$bookId], $edition === ManualBook::EDITION_TEACHER ? 'TG' : 'ST', number_format($sum, 1), number_format($stored));
                    }

                    $summaryKey = "zone_{$edition}";
                    if ($stored !== null) {
                        $summary[$summaryKey] = ($summary[$summaryKey] ?? 0) + $stored;
                    }

                    foreach ($this->roundToTotal($values, $target) as $woredaKey => $quantity) {
                        if ($quantity <= 0) {
                            continue;
                        }

                        $result['rows'][] = [
                            'zone_id' => $zoneId,
                            'woreda_key' => $woredaKey,
                            'woreda' => $woredaNames[$zoneId][$woredaKey],
                            'manual_book_id' => $bookId,
                            'edition' => $edition,
                            'quantity' => $quantity,
                            'sheet' => $sources[$zoneId][$bookId][$edition][$woredaKey]['sheet'] ?? null,
                            'row' => $sources[$zoneId][$bookId][$edition][$woredaKey]['row'] ?? null,
                        ];
                        $summary[$edition] += $quantity;
                    }
                }
            }

            $result['zones'][$zone->name] = $summary;
        }

        ksort($result['zones']);
        $result['woreda_count'] = collect($woredaNames)->sum(fn ($names) => count($names));
        $result['new_woredas'] = collect($result['zones'])->sum('new_woredas');
        $result['line_count'] = count($result['rows']);
        $result['title_count'] = count($titles);
        $result['total'] = (int) collect($result['rows'])->sum('quantity');

        if (! $result['rows']) {
            $result['errors'][] = 'No woreda quantity in the workbook matched a zone and an allocated title.';
        }

        return $result;
    }

    /**
     * Saves the split for the year: creates the woredas the workbook names, and replaces the
     * woreda allocation of every zone it covers.
     */
    public function commit(?User $actor, string $path, string $fileName, string $regionName, string $year): array
    {
        $result = $this->parse($path, $regionName, $year);

        if ($result['errors']) {
            return $result + ['import' => null];
        }

        $result['import'] = DB::transaction(function () use ($actor, $result, $fileName, $year): ManualAllocationImport {
            $import = ManualAllocationImport::create([
                'file_name' => $fileName,
                'academic_year' => $year,
                'level' => 'woreda',
                'imported_by' => $actor?->id,
                'line_count' => $result['line_count'],
                'title_count' => $result['title_count'],
                'total_quantity' => $result['total'],
                'region_totals' => ['region' => $result['region'], 'zones' => $result['zones'], 'sheets' => $result['sheets']],
                'warnings' => $result['warnings'],
            ]);

            $zoneIds = array_values(array_unique(array_column($result['rows'], 'zone_id')));
            $zones = Zone::query()->whereIn('id', $zoneIds)->get()->keyBy('id');

            $woredaIds = [];
            foreach ($result['rows'] as $row) {
                $key = $row['zone_id'] . ':' . $row['woreda_key'];
                if (isset($woredaIds[$key])) {
                    continue;
                }

                $woreda = $this->woredaIndex[$row['zone_id']][$row['woreda_key']] ?? null;
                $woreda ??= Woreda::query()->firstOrCreate(
                    ['name' => $row['woreda'], 'zone_id' => $row['zone_id']],
                    ['region_id' => $this->region->id, 'country_id' => $zones[$row['zone_id']]->country_id ?? $this->region->country_id]
                );
                $woredaIds[$key] = $woreda->id;
            }

            ManualWoredaAllocation::query()
                ->where('academic_year', $year)
                ->where('region_id', $this->region->id)
                ->whereIn('zone_id', $zoneIds)
                ->delete();

            $now = now();
            foreach (array_chunk($result['rows'], 500) as $chunk) {
                ManualWoredaAllocation::query()->insert(array_map(fn (array $row) => [
                    'manual_allocation_import_id' => $import->id,
                    'academic_year' => $year,
                    'region_id' => $this->region->id,
                    'zone_id' => $row['zone_id'],
                    'woreda_id' => $woredaIds[$row['zone_id'] . ':' . $row['woreda_key']],
                    'manual_book_id' => $row['manual_book_id'],
                    'edition' => $row['edition'],
                    'quantity' => $row['quantity'],
                    'source_sheet' => $row['sheet'],
                    'source_row' => $row['row'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ], $chunk));
            }

            return $import;
        });

        return $result;
    }

    /**
     * Whole copies that add up to the total, in proportion to the shares: each share is
     * scaled to the total and floored, and the copies left over go to the largest remainders.
     *
     * @param  array<string, float>  $shares
     * @return array<string, int>
     */
    public function roundToTotal(array $shares, int $total): array
    {
        $sum = array_sum($shares);
        if ($sum <= 0 || $total <= 0) {
            return array_map(fn () => 0, $shares);
        }

        $rounded = [];
        $remainders = [];
        foreach ($shares as $key => $share) {
            $exact = $share * $total / $sum;
            $rounded[$key] = (int) floor($exact + 1e-9);
            $remainders[$key] = $exact - $rounded[$key];
        }

        arsort($remainders);
        $left = $total - array_sum($rounded);
        foreach (array_keys($remainders) as $key) {
            if ($left <= 0) {
                break;
            }
            $rounded[$key]++;
            $left--;
        }

        return $rounded;
    }

    /**
     * Every sheet's caption, STB/TG columns and woreda lines.
     *
     * @return array<string, array{caption: string, columns: array<int, array{index: int, grade: int, edition: string}>, lines: array<int, array>}>
     */
    private function readSheets(string $path): array
    {
        $options = new Options();
        $options->SHOULD_FORMAT_DATES = false;
        $reader = new Reader($options);
        $reader->open($path);

        $sheets = [];
        try {
            foreach ($reader->getSheetIterator() as $sheet) {
                $name = trim($sheet->getName());
                $rows = [];
                foreach ($sheet->getRowIterator() as $row) {
                    $rows[] = array_map(fn ($cell) => $this->cellValue($cell), $row->getCells());
                }

                $sheets[$name] = $this->readSheet($rows);
            }
        } finally {
            $reader->close();
        }

        return $sheets;
    }

    private function readSheet(array $rows): array
    {
        $caption = '';
        $columns = [];
        $start = null;

        // The edition row (STB / TG) sits under the grade row; captions come before both
        foreach ($rows as $index => $values) {
            $editions = [];
            foreach ($values as $column => $value) {
                if ($edition = $this->editionOf((string) $value)) {
                    $editions[$column] = $edition;
                }
            }

            if (count($editions) < 2) {
                if ($index < 5) {
                    $caption .= ' ' . implode(' ', array_filter(array_map(fn ($value) => is_string($value) ? $value : '', $values)));
                }

                continue;
            }

            $grades = $rows[$index - 1] ?? [];
            $grade = null;
            $keys = [];
            for ($column = 0; $column <= max(array_keys($editions)); $column++) {
                $header = $grades[$column] ?? '';
                if ($header !== '' && $header !== null) {
                    $grade = $this->gradeNumber((string) $header);
                    $keys = $this->gradeKeys((string) $header);
                }
                if (isset($editions[$column]) && $grade !== null) {
                    $columns[] = ['index' => $column, 'grade' => $grade, 'keys' => $keys, 'edition' => $editions[$column]];
                }
            }

            $start = $index + 1;
            break;
        }

        $lines = [];
        $zoneLabel = '';
        $zone = null;
        $skip = array_map(fn ($word) => Str::lower($word), config('manual_allocation.woreda_skip_rows', []));

        foreach (array_slice($rows, $start ?? count($rows), null, true) as $index => $values) {
            $first = $this->clean((string) ($values[0] ?? ''));
            $woreda = $this->clean((string) ($values[1] ?? ''));

            if (in_array(Str::lower($first), $skip, true) || in_array(Str::lower($woreda), $skip, true)) {
                continue;
            }
            if ($first !== '') {
                $zoneLabel = $first;
                $zone = $this->zoneFor($first);
            }
            if ($woreda === '' || is_numeric($woreda)) {
                continue;
            }

            $lines[] = [
                'row' => $index + 1,
                'zone_label' => $zoneLabel,
                'zone' => $zone,
                'woreda' => $this->woredaName($woreda),
                'woreda_key' => $this->woredaKey($woreda),
                'values' => $values,
            ];
        }

        return ['caption' => trim($caption), 'columns' => $columns, 'lines' => $lines];
    }

    /**
     * The allocated titles' subject names for a sheet, from the first keyword its name or caption holds.
     *
     * @return array<int, string>
     */
    private function sheetSubjects(string $text): array
    {
        $text = Str::lower(preg_replace('/\s+/', ' ', $text));

        foreach (config('manual_allocation.woreda_sheets', []) as $keyword => $subjects) {
            if (str_contains($text, Str::lower($keyword))) {
                return (array) $subjects;
            }
        }

        return [];
    }

    private function allocatedBooks(string $year)
    {
        return ManualBook::query()
            ->whereIn('id', ManualAllocation::query()->where('academic_year', $year)->where('region_id', $this->region->id)->select('manual_book_id'))
            ->get(['id', 'title', 'subject_name', 'grade_name']);
    }

    private function indexPlaces(): void
    {
        $this->zoneIndex = Zone::query()->where('region_id', $this->region->id)->get()
            ->keyBy(fn (Zone $zone) => Str::lower($this->clean($zone->name)))->all();

        $this->woredaIndex = [];
        Woreda::query()->whereIn('zone_id', collect($this->zoneIndex)->pluck('id'))->get()
            ->each(function (Woreda $woreda): void {
                $this->woredaIndex[$woreda->zone_id][$this->woredaKey($woreda->name)] = $woreda;
            });
    }

    /**
     * "Arsii  Aanaa 28" is Arsii; "Godina Jimma" maps through the zone aliases onto "Jimma Zone".
     */
    private function zoneFor(string $label): ?Zone
    {
        $name = trim(preg_replace(config('manual_allocation.woreda_zone_suffix_pattern'), '', $label));
        $aliases = config('manual_allocation.zones', []);

        foreach ([$label, $name] as $written) {
            $written = Str::lower($this->clean($written));

            // An alias is one more spelling to try: a database may name the zone as the workbook does
            foreach (array_unique(array_filter([Str::lower($aliases[$written] ?? ''), $written])) as $candidate) {
                foreach ([$candidate, "{$candidate} zone", preg_replace('/\s+zone$/', '', $candidate)] as $spelling) {
                    if (isset($this->zoneIndex[$spelling])) {
                        return $this->zoneIndex[$spelling];
                    }
                }
            }
        }

        return null;
    }

    private function woredaName(string $name): string
    {
        $name = $this->clean($name);

        return config('manual_allocation.woredas')[Str::lower($name)] ?? $name;
    }

    // "B/M/ Mattuu " and "B/M/Mattuu" are the same woreda
    private function woredaKey(string $name): string
    {
        return Str::lower(preg_replace('/\s*\/\s*/', '/', $this->woredaName($name)));
    }

    private function editionOf(string $value): ?string
    {
        $value = Str::lower(trim($value));

        return match (true) {
            in_array($value, ['stb', 'st', 'sb', 'student', 'student book', 'practs book', 'practice book'], true) => ManualBook::EDITION_STUDENT,
            in_array($value, ['tg', 'teacher guide', "teacher's guide"], true) => ManualBook::EDITION_TEACHER,
            default => null,
        };
    }

    // 1, "G5", "Grade 5", "Level 5 (I)"
    private function gradeNumber(string $value): ?int
    {
        return preg_match('/\d+/', $value, $match) ? (int) $match[0] : null;
    }

    /**
     * Ways a grade is written, so a column and a title's grade can be matched: the number, and
     * for pre-primary the level. The bureau writes "Level 5 (I)" (age 5, level I), the printer
     * "Level 1", the Ministry "G5"; the first is level 1 and number 5.
     *
     * @return array<int, string>
     */
    private function gradeKeys(string $value): array
    {
        $value = Str::lower(trim($value));
        $keys = [];

        if (($number = $this->gradeNumber($value)) !== null) {
            $keys[] = (string) $number;
        }

        if (preg_match('/\((i{1,3})\)/', $value, $match)) {
            $keys[] = 'L' . strlen($match[1]);
        } elseif (preg_match('/(?:level|kg\s*l?)\s*(\d)/', $value, $match)) {
            $keys[] = 'L' . $match[1];
        }

        return $keys;
    }

    private function clean(string $value): string
    {
        return trim(preg_replace('/\s+/u', ' ', $value));
    }

    private function cellValue($cell): mixed
    {
        $value = $cell instanceof FormulaCell ? $cell->getComputedValue() : $cell->getValue();

        return $value instanceof \DateTimeInterface ? $value->format('Y-m-d') : $value;
    }
}
