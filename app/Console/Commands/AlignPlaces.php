<?php

namespace App\Console\Commands;

use App\Services\ManualTracking\PlaceAlignment;
use Illuminate\Console\Command;

class AlignPlaces extends Command
{
    protected $signature = 'manual:align-places
        {map : JSON map of region renames, zone moves and printer zone codes}
        {--commit : Save the changes (without it, only shows what would change)}';

    protected $description = 'Line regions and zones up with the printer\'s data from a reviewed map, before importing it';

    public function handle(PlaceAlignment $alignment): int
    {
        $path = $this->argument('map');
        $map = is_file($path) ? json_decode((string) file_get_contents($path), true) : null;
        if (! is_array($map)) {
            $this->error("Cannot read a JSON map from {$path}.");

            return self::FAILURE;
        }

        $report = $alignment->apply($map, (bool) $this->option('commit'));

        foreach ($report['changes'] as $change) {
            $this->line("  {$change}");
        }
        foreach ($report['errors'] as $error) {
            $this->error("  {$error}");
        }

        if ($report['errors']) {
            $this->error('Nothing was saved: fix the map or the data first.');

            return self::FAILURE;
        }

        $count = count($report['changes']);
        $this->info($this->option('commit')
            ? ($count ? "Saved {$count} changes." : 'Already aligned; nothing to change.')
            : "{$count} changes would be made. Run again with --commit to save them.");

        return self::SUCCESS;
    }
}
