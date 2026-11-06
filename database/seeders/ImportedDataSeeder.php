<?php

namespace Database\Seeders;

use App\Models\Region;
use App\Services\ManualTracking\AllocationImportService;
use App\Services\ManualTracking\ImportedDataReset;
use App\Services\ManualTracking\PrinterDataImportService;
use App\Services\ManualTracking\WoredaAllocationImportService;
use App\Support\AcademicYear;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * SEED_PROFILE=imports: places, titles, grades, subjects, book QR codes, allocation and cartons
 * come only from the workbooks in storage/app/allocation-imports:
 *
 * - printer/: the printer's data, one Printer_Data_Requirement-<REGION>.xlsx per region. It is
 *   the base: titles, book QR codes, the zone allocation and the cartons packed per zone.
 * - ministry/: the Ministry's allocation workbooks, read in name order, only when printer/ is
 *   empty (the printer data replaces them).
 * - woreda/: regional bureau woreda splits, each named starting with its region ("Oromia
 *   Education Bureau … .xlsx"), matched onto the titles above.
 *
 * ALLOCATION_YEAR sets the academic year.
 */
class ImportedDataSeeder extends Seeder
{
    public function run(
        PrinterDataImportService $printer,
        AllocationImportService $ministry,
        WoredaAllocationImportService $woredas,
        ImportedDataReset $reset
    ): void {
        $root = storage_path('app/allocation-imports');
        $year = (string) ($_ENV['ALLOCATION_YEAR'] ?? $_SERVER['ALLOCATION_YEAR'] ?? AcademicYear::current());

        // The year the workbooks are imported for must be one screens can pick
        \App\Models\AcademicYear::ensure((int) $year);

        $printerFiles = array_values(array_filter(glob("{$root}/printer/*.xlsx") ?: [], fn ($path) => ! str_starts_with(basename($path), '~$')));

        if ($printerFiles) {
            // A region's file holds 400,000 carton rows; reading it streams, but the totals take room
            ini_set('memory_limit', '1G');

            foreach ($printerFiles as $path) {
                $result = $printer->commit(null, $path, basename($path), $year);
                if ($result['errors']) {
                    throw new RuntimeException(basename($path) . ': ' . implode('; ', array_slice($result['errors'], 0, 5)));
                }
                $this->command?->info(sprintf('%s: %s, %d titles, %d zones, %s copies in %s cartons.',
                    basename($path), $result['region'], $result['title_count'], count($result['zones']),
                    number_format($result['total']), number_format($result['cartons'])));
            }
        } else {
            $ministryFiles = glob("{$root}/ministry/*.xlsx") ?: [];
            if (! $ministryFiles) {
                throw new RuntimeException("No printer data in {$root}/printer and no Ministry allocation workbook in {$root}/ministry.");
            }

            foreach ($ministryFiles as $path) {
                $result = $ministry->commit(null, $path, basename($path), $year);
                if ($result['errors']) {
                    throw new RuntimeException(basename($path) . ': ' . implode('; ', array_slice($result['errors'], 0, 5)));
                }
                $this->command?->info(sprintf('%s: %d titles, %s copies.', basename($path), count($result['titles']), number_format($result['total'])));
            }
        }

        $regions = Region::query()->pluck('name')->sortByDesc(fn ($name) => strlen($name));
        foreach (glob("{$root}/woreda/*.xlsx") ?: [] as $path) {
            $region = $regions->first(fn ($name) => Str::startsWith(Str::lower(basename($path)), Str::lower($name)));
            if (! $region) {
                throw new RuntimeException(basename($path) . ': the file name does not start with a region name.');
            }

            $result = $woredas->commit(null, $path, basename($path), $region, $year);
            if ($result['errors']) {
                throw new RuntimeException(basename($path) . ': ' . implode('; ', $result['errors']));
            }
            $this->command?->info(sprintf('%s: %d woredas, %s copies.', basename($path), $result['woreda_count'], number_format($result['total'])));
            foreach (array_slice($result['warnings'], 0, 10) as $warning) {
                $this->command?->warn('  ' . $warning);
            }
        }

        $reset->run();
    }
}
