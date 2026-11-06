<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\ManualTracking\PrinterDataImportService;
use App\Support\AcademicYear;
use Illuminate\Console\Command;

class ImportPrinterData extends Command
{
    protected $signature = 'manual:import-printer-data
        {files* : Printer data workbooks (Printer_Data_Requirement-<REGION>.xlsx), or a folder holding them}
        {--year= : Ethiopian academic year the data is for (default: the current one)}
        {--user= : Email of the user recorded as importer}
        {--commit : Save the data (without it, only checks the files)}';

    protected $description = 'Check or import the printer\'s books, book QR codes, zone allocation and cartons, one workbook per region';

    public function handle(PrinterDataImportService $service): int
    {
        $year = (string) ($this->option('year') ?: AcademicYear::current());
        if (! AcademicYear::isKnown((int) $year)) {
            $this->error("Academic year {$year} is not set up. Add it on the Academic Years page first.");

            return self::FAILURE;
        }

        $paths = collect($this->argument('files'))
            ->flatMap(fn (string $path) => is_dir($path) ? (glob(rtrim($path, '/') . '/*.xlsx') ?: []) : [$path])
            ->reject(fn (string $path) => str_starts_with(basename($path), '~$'))
            ->values();

        if ($paths->isEmpty()) {
            $this->error('No workbook to read.');

            return self::FAILURE;
        }

        $actor = $this->option('user') ? User::query()->where('email', $this->option('user'))->first() : null;
        $failed = false;
        $summary = [];

        foreach ($paths as $path) {
            if (! is_file($path)) {
                $this->error("File not found: {$path}");
                $failed = true;

                continue;
            }

            $result = $this->option('commit')
                ? $service->commit($actor, $path, basename($path), $year)
                : $service->parse($path);

            $this->line(sprintf('<info>%s</info>: %s, %d titles, %d zones, %s student books, %s teacher guides, %s cartons.',
                basename($path), $result['region'] ?? '?', $result['title_count'], count($result['zones']),
                number_format($result['student']), number_format($result['teacher']), number_format($result['cartons'])));

            foreach ($result['warnings'] as $warning) {
                $this->warn('  ' . $warning);
            }

            foreach ($result['errors'] as $error) {
                $this->error('  ' . $error);
            }

            $failed = $failed || (bool) $result['errors'];
            $summary[] = [$result['region'] ?? basename($path), $result['title_count'], count($result['zones']),
                number_format($result['student']), number_format($result['teacher']), number_format($result['cartons']),
                $result['errors'] ? 'errors' : ($this->option('commit') ? 'saved' : 'checked')];
        }

        if (count($summary) > 1) {
            $this->table(['Region', 'Titles', 'Zones', 'Student books', 'Teacher guides', 'Cartons', 'Result'], $summary);
        }

        if ($failed) {
            $this->error('Files with errors were not saved: fix them and run again.');

            return self::FAILURE;
        }

        $this->info($this->option('commit')
            ? "Printer data for {$year} saved. Re-import any regional woreda split (manual:import-woreda-allocations) so it follows the new titles."
            : 'Files checked; nothing saved. Run again with --commit to import.');

        return self::SUCCESS;
    }
}
