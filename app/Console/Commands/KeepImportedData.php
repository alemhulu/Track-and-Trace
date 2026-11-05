<?php

namespace App\Console\Commands;

use App\Services\ManualTracking\ImportedDataReset;
use Illuminate\Console\Command;

class KeepImportedData extends Command
{
    protected $signature = 'manual:keep-imported-data {--force : Run without asking}';

    protected $description = 'Delete seeded demo data and keep only what the imported allocation workbooks produced';

    public function handle(ImportedDataReset $reset): int
    {
        $this->warn('This deletes manual-tracking activity, the print module\'s books, orders and packages, organizations, warehouses,');
        $this->warn('every user except ' . implode(' and ', ImportedDataReset::KEPT_USERS) . ', and the regions, zones, woredas, grades and subjects');
        $this->warn('that no imported workbook uses. Back up the database first.');

        if (! $this->option('force') && ! $this->confirm('Continue?')) {
            return self::FAILURE;
        }

        $removed = $reset->run();

        $this->table(['Table', 'Rows removed'], collect($removed)->map(fn ($rows, $table) => [$table, number_format($rows)])->values()->all());
        $this->info('Only imported data is left.');

        return self::SUCCESS;
    }
}
