<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\ManualTracking\AllocationImportService;
use App\Support\AcademicYear;
use Illuminate\Console\Command;

class ImportManualAllocations extends Command
{
    protected $signature = 'manual:import-allocations
        {file : Path to the Ministry allocation workbook (.xlsx)}
        {--year= : Ethiopian academic year the allocation is for (default: the current one)}
        {--user= : Email of the user recorded as importer}
        {--commit : Save the allocation (without it, only checks the file)}';

    protected $description = 'Check or import the regional student text and teacher guide allocation';

    public function handle(AllocationImportService $service): int
    {
        $path = $this->argument('file');
        if (! is_file($path)) {
            $this->error("File not found: {$path}");

            return self::FAILURE;
        }

        $year = (string) ($this->option('year') ?: AcademicYear::current());
        if (! AcademicYear::isKnown((int) $year)) {
            $this->error("Academic year {$year} is not set up. Add it on the Academic Years page first.");

            return self::FAILURE;
        }

        $actor = $this->option('user') ? User::query()->where('email', $this->option('user'))->first() : null;

        $result = $this->option('commit')
            ? $service->commit($actor, $path, basename($path), $year)
            : $service->parse($path, $year);

        $this->table(
            ['Region', 'Lines', 'Titles', 'Student texts', 'Teacher guides', 'Total', 'Summary total'],
            collect($result['regions'])->map(fn ($region, $name) => [
                $name,
                $region['lines'],
                $region['titles'],
                number_format($region['student']),
                number_format($region['teacher']),
                number_format($region['student'] + $region['teacher']),
                $region['expected_total'] === null ? '-' : number_format($region['expected_total']),
            ])->values()->all()
        );

        if ($result['zones']) {
            $this->table(
                ['Region', 'Zone', 'Lines', 'Titles', 'Student texts', 'Teacher guides', 'Total', ''],
                collect($result['zones'])->flatMap(fn ($zones, $region) => collect($zones)->map(fn ($zone, $name) => [
                    $region,
                    $name,
                    $zone['lines'],
                    $zone['titles'],
                    number_format($zone['student']),
                    number_format($zone['teacher']),
                    number_format($zone['student'] + $zone['teacher']),
                    $zone['zone_id'] ? '' : 'new',
                ])->values())->all()
            );
        }

        $this->line(sprintf('%d lines, %d titles, %s copies.', $result['line_count'], count($result['titles']), number_format($result['total'])));

        foreach ($result['warnings'] as $warning) {
            $this->warn($warning);
        }

        foreach ($result['errors'] as $error) {
            $this->error($error);
        }

        if ($result['errors']) {
            $this->error('Nothing was saved: fix the errors above and run again.');

            return self::FAILURE;
        }

        $this->info($this->option('commit')
            ? "Allocation for {$year} saved (import #{$result['import']->id})."
            : 'File checked; nothing saved. Run again with --commit to import.');

        return self::SUCCESS;
    }
}
