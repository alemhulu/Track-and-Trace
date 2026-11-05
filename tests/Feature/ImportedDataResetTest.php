<?php

namespace Tests\Feature;

use App\Models\ManualTracking\ManualAllocation;
use App\Models\ManualTracking\ManualBook;
use App\Models\Organization;
use App\Models\Region;
use App\Models\User;
use App\Models\Woreda;
use App\Models\Zone;
use App\Services\ManualTracking\AllocationImportService;
use App\Services\ManualTracking\ImportedDataReset;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;
use Tests\TestCase;

/**
 * After the reset only what an imported workbook brought is left: its titles and
 * allocation, the places it names, and the grades and subjects its titles use.
 */
class ImportedDataResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_data_goes_and_imported_data_stays(): void
    {
        Storage::fake('public');
        $_ENV['SEED_PROFILE'] = 'showcase';
        $this->seed(DatabaseSeeder::class);
        unset($_ENV['SEED_PROFILE']);

        $path = tempnam(sys_get_temp_dir(), 'reset') . '.xlsx';
        $writer = new Writer();
        $writer->openToFile($path);
        $writer->getCurrentSheet()->setName('Oromia');
        foreach ([
            ['Region', 'Language', 'TYPE', 'SUBJECT', 'Grade', 'Book title, grade level  and type of the book', 'Final Quantity'],
            ['Oromia', 'Afaan Oromo', 'ST', 'Mathematics', 'G1', 'Mathes Grade 1Student book', 1000],
        ] as $row) {
            $writer->addRow(Row::fromValues($row));
        }
        $writer->addNewSheetAndMakeItCurrent()->setName('Arsii');
        foreach ([
            ['Arsii'],
            ['Region', 'Language', 'TYPE', 'SUBJECT', 'Grade', 'Book title, grade level  and type of the book', 'Final Quantity'],
            ['Oromia', 'Afaan Oromo', 'ST', 'Mathematics', 'G1', 'Mathes Grade 1Student book', 400],
        ] as $row) {
            $writer->addRow(Row::fromValues($row));
        }
        $writer->close();
        app(AllocationImportService::class)->commit(null, $path, 'ministry.xlsx', '2019');

        $this->assertGreaterThan(0, DB::table('manual_distributions')->count());
        $this->assertGreaterThan(0, Organization::query()->count());

        app(ImportedDataReset::class)->run();

        $this->assertSame(0, DB::table('manual_distributions')->count());
        $this->assertSame(0, DB::table('student_book_issues')->count());
        $this->assertSame(0, DB::table('books')->count());
        $this->assertSame(0, Organization::query()->count());
        $this->assertEqualsCanonicalizing(ImportedDataReset::KEPT_USERS, User::query()->pluck('email')->all());

        $this->assertSame(['Mathes Grade 1Student book'], ManualBook::query()->pluck('title')->all());
        $this->assertSame(2, ManualAllocation::query()->count());
        $this->assertSame(['Oromia'], Region::query()->pluck('name')->all());
        $this->assertSame(['Arsii'], Zone::query()->pluck('name')->all());
        $this->assertSame(0, Woreda::query()->count());
        $this->assertSame(['1'], DB::table('grades')->pluck('name')->all());
        $this->assertSame(['Mathematics'], DB::table('subjects')->pluck('name')->all());

        // The kept admin can still open the allocation page
        $this->actingAs(User::query()->where('email', 'admin@gmail.com')->firstOrFail())
            ->get(route('manual-tracking.allocations.index'))
            ->assertOk()
            ->assertSee('Mathes Grade 1Student book');
    }
}
