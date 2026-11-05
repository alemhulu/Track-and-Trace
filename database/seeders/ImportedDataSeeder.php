<?php

namespace Database\Seeders;

use App\Models\Region;
use App\Services\ManualTracking\AllocationImportService;
use App\Services\ManualTracking\ImportedDataReset;
use App\Services\ManualTracking\WoredaAllocationImportService;
use App\Support\AcademicYear;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * SEED_PROFILE=imports: places, titles, grades, subjects and allocation come only from the
 * workbooks in storage/app/allocation-imports. Ministry workbooks go in ministry/ (read in
 * name order), regional bureau woreda splits in woreda/, each named starting with its
 * region ("Oromia Education Bureau … .xlsx"). ALLOCATION_YEAR sets the academic year.
 */
class ImportedDataSeeder extends Seeder
{
    public function run(AllocationImportService $ministry, WoredaAllocationImportService $woredas, ImportedDataReset $reset): void
    {
        $root = storage_path('app/allocation-imports');
        $year = (string) ($_ENV['ALLOCATION_YEAR'] ?? $_SERVER['ALLOCATION_YEAR'] ?? AcademicYear::current());

        // The year the workbooks are imported for must be one screens can pick
        \App\Models\AcademicYear::ensure((int) $year);

        $ministryFiles = glob("{$root}/ministry/*.xlsx") ?: [];
        if (! $ministryFiles) {
            throw new RuntimeException("No Ministry allocation workbook in {$root}/ministry.");
        }

        foreach ($ministryFiles as $path) {
            $result = $ministry->commit(null, $path, basename($path), $year);
            if ($result['errors']) {
                throw new RuntimeException(basename($path) . ': ' . implode('; ', array_slice($result['errors'], 0, 5)));
            }
            $this->command?->info(sprintf('%s: %d titles, %s copies.', basename($path), count($result['titles']), number_format($result['total'])));
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
        }

        $reset->run();
    }
}
