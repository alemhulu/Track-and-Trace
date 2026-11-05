<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\ManualTracking\WoredaAllocationImportService;
use App\Support\AcademicYear;
use Illuminate\Console\Command;

class ImportWoredaAllocations extends Command
{
    protected $signature = 'manual:import-woreda-allocations
        {file : Path to the regional bureau woreda distribution workbook (.xlsx)}
        {--region=Oromia : Region the workbook splits}
        {--year= : Ethiopian academic year the allocation is for (default: the current one)}
        {--user= : Email of the user recorded as importer}
        {--commit : Save the split (without it, only checks the file)}';

    protected $description = 'Check or import a region\'s student text and teacher guide allocation per woreda';

    public function handle(WoredaAllocationImportService $service): int
    {
        $path = $this->argument('file');
        if (! is_file($path)) {
            $this->error("File not found: {$path}");

            return self::FAILURE;
        }

        $year = (string) ($this->option('year') ?: AcademicYear::current());
        $region = (string) $this->option('region');
        $actor = $this->option('user') ? User::query()->where('email', $this->option('user'))->first() : null;

        $result = $this->option('commit')
            ? $service->commit($actor, $path, basename($path), $region, $year)
            : $service->parse($path, $region, $year);

        if ($result['sheets']) {
            $this->table(
                ['Sheet', 'Subject', 'Woreda rows', 'Titles matched', 'Missing'],
                collect($result['sheets'])->map(fn ($sheet, $name) => [
                    $name, $sheet['subject'] ?? '-', $sheet['rows'], count($sheet['titles']), implode(', ', $sheet['missing']),
                ])->values()->all()
            );
        }

        if ($result['zones']) {
            $this->table(
                ['Zone', 'Woredas', 'New', 'Student texts', 'Zone ST', 'Teacher guides', 'Zone TG'],
                collect($result['zones'])->map(fn ($zone, $name) => [
                    $name,
                    $zone['woredas'],
                    $zone['new_woredas'],
                    number_format($zone['student']),
                    $zone['zone_student'] === null ? '-' : number_format($zone['zone_student']),
                    number_format($zone['teacher']),
                    $zone['zone_teacher'] === null ? '-' : number_format($zone['zone_teacher']),
                ])->values()->all()
            );
        }

        $this->line(sprintf('%s: %d woredas (%d new), %d lines, %d titles, %s copies.',
            $result['region'] ?? $region, $result['woreda_count'], $result['new_woredas'], $result['line_count'], $result['title_count'], number_format($result['total'])));

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
            ? "Woreda allocation for {$year} saved (import #{$result['import']->id})."
            : 'File checked; nothing saved. Run again with --commit to import.');

        return self::SUCCESS;
    }
}
