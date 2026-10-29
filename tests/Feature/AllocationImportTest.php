<?php

namespace Tests\Feature;

use App\Http\Livewire\ManualTracking\Allocation\AllocationIndex;
use App\Models\ManualTracking\ManualAllocation;
use App\Models\ManualTracking\ManualBook;
use App\Models\Region;
use App\Models\User;
use App\Models\Zone;
use App\Services\ManualTracking\AllocationImportService;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\Showcase\ShowcaseData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;
use Tests\TestCase;

/**
 * The Ministry allocation workbook: columns found by header on both sheet layouts, ST and TG
 * rows paired into one title named as the workbook writes it, second-language editions kept apart, TBD types flagged,
 * repeated rows summed, totals checked against the Summary sheet, and the result scoped
 * to the viewer's region.
 */
class AllocationImportTest extends TestCase
{
    use RefreshDatabase;

    private string $workbook;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $_ENV['SEED_PROFILE'] = 'showcase';
        $this->seed(DatabaseSeeder::class);
        unset($_ENV['SEED_PROFILE']);

        $this->workbook = $this->writeWorkbook([
            'Oromia' => [
                ['Region', 'Language', 'TYPE', 'SUBJECT', 'Grade', 'Book title, grade level  and type of the book', 'Final Quantity'],
                ['Oromia', 'Afaan Oromo', 'ST', 'Mathematics', 'G1', 'Mathematics Grade 1 Student book', 1000],
                ['Oromia', 'Afaan Oromo', 'TG', 'Mathematics', 'G1', 'Mathematics Grade 1 Teachers Guide', 25],
                ['Oromia', 'Amharic', 'ST', 'Amharic', 'G5', 'Amaric 2nd Language Grade 5 Student book', 300],
                ['Oromia', 'Amharic', 'ST', 'Ahmaric', 'G5', 'Amharic Grade 5 Student book', 200],
                ['Oromia Distribution'],
                ['Total Sum', '', '', '', '', '', 1525],
            ],
            'Amhara' => [
                ['Region', 'S.No', 'Language', 'SN Code', 'N SN CODE', 'TYPE', 'SUBJECT', 'Grade', 'Book title, grade level  and type of the book', 'SPEC', 'Final Quantity'],
                ['Amhara', 1, 'AWI', 'TBD', 'N1001', 'TBD', 'HP', 'G1', 'Amhara HPE G1', 'A4', 500],
                ['Amhara', 2, 'Amharic', 'TBD', '', 'ST', 'Environmental Science', 'Level 2', 'Environmental Science Level 2 ST', 'A4', 100],
                ['Amhara', 3, 'AWI', 'TBD', '', 'TBD', 'HP', 'G1', 'Amhara HPE G1', 'A4', 50],
                ['Total Sum', '', '', '', '', '', '', '', '', '', 650],
            ],
            'Summary' => [
                ['Summary'],
                ['No', 'Region Name', 'Number of items', 'Total Quantity to be printed and distributed'],
                [1, 'Oromia', 4, 1525],
                [2, 'Amhara', 3, 999],
                [3, 'South West', 10, 5000],
                ['', 'Grand Total', 17, 7524],
            ],
        ]);
    }

    public function test_workbook_is_read_paired_and_checked_against_the_summary(): void
    {
        $result = app(AllocationImportService::class)->parse($this->workbook);

        $this->assertSame([], $result['errors']);
        $this->assertSame(7, $result['line_count']);
        $this->assertSame(2175, $result['total']);
        $this->assertCount(5, $result['titles']);

        $oromia = $result['regions']['Oromia'];
        $this->assertSame([3, 1500, 25, 1525], [$oromia['titles'], $oromia['student'], $oromia['teacher'], $oromia['expected_total']]);
        $this->assertSame(2, $result['regions']['Amhara']['unconfirmed']);
        $this->assertArrayHasKey('South West Ethiopia', $result['awaiting']);

        $warnings = implode("\n", $result['warnings']);
        $this->assertStringContainsString('repeats Amhara row 2', $warnings);
        $this->assertStringContainsString('the Summary says 3 and 999', $warnings);
        $this->assertStringContainsString('South West Ethiopia: listed in the Summary', $warnings);
        $this->assertStringContainsString('2 rows had no clear type', $warnings);
    }

    public function test_commit_saves_titles_and_allocation_and_is_repeatable(): void
    {
        $service = app(AllocationImportService::class);
        $service->commit(null, $this->workbook, 'allocation.xlsx', '2019');

        $math = ManualBook::query()->where('title', 'Mathematics Grade 1 Student book')->firstOrFail();
        $this->assertTrue($math->has_teacher_guide);
        $this->assertSame(['Afaan Oromo', 'Mathematics', 'G1'], [$math->language, $math->subject_name, $math->grade_name]);
        $this->assertSame([ManualBook::EDITION_STUDENT => 1000, ManualBook::EDITION_TEACHER => 25],
            $math->allocations()->pluck('quantity', 'edition')->map(fn ($quantity) => (int) $quantity)->sortKeys()->all());

        $this->assertTrue(ManualBook::query()->where('title', 'Amaric 2nd Language Grade 5 Student book')->exists());
        $this->assertTrue(ManualBook::query()->where('title', 'Amharic Grade 5 Student book')->exists());
        $this->assertTrue(ManualBook::query()->where('title', 'Environmental Science Level 2 ST')->exists());

        $hpe = ManualBook::query()->where('title', 'Amhara HPE G1')->firstOrFail();
        $this->assertSame(['AWI', 'HP'], [$hpe->language, $hpe->subject_name]);
        $this->assertFalse($hpe->has_teacher_guide);
        $this->assertSame('N1001', $hpe->print_code);
        $allocation = $hpe->allocations()->sole();
        $this->assertSame(550, $allocation->quantity);
        $this->assertFalse($allocation->type_confirmed);

        $counts = fn () => [ManualBook::query()->whereNotNull('source_key')->count(), ManualAllocation::query()->count(), (int) ManualAllocation::query()->sum('quantity')];
        $this->assertSame([5, 6, 2175], $counts());

        $service->commit(null, $this->workbook, 'allocation.xlsx', '2019');
        $this->assertSame([5, 6, 2175], $counts());
    }

    public function test_unknown_region_stops_the_import(): void
    {
        $path = $this->writeWorkbook(['Atlantis' => [
            ['Region', 'Language', 'TYPE', 'SUBJECT', 'Grade', 'Book title, grade level  and type of the book', 'Final Quantity'],
            ['Atlantis', 'English', 'ST', 'English', 'G1', 'English Grade 1 Student book', 10],
            ['Oromia', 'English', 'ST', 'English', 'G9X', 'English Student book', 10],
        ]]);

        $result = app(AllocationImportService::class)->commit(null, $path, 'bad.xlsx', '2019');

        $this->assertNull($result['import']);
        $this->assertStringContainsString('region "Atlantis" is not set up', $result['errors'][0]);
        $this->assertStringContainsString('unknown grade "G9X"', $result['errors'][1]);
        $this->assertSame(0, ManualAllocation::query()->count());
    }

    public function test_page_is_scoped_by_region_and_only_admins_import(): void
    {
        $officer = $this->user('moe.officer');
        $zone = $this->user('east.shewa.zone');

        Livewire::actingAs($officer)->test(AllocationIndex::class)
            ->set('workbook', UploadedFile::fake()->createWithContent('allocation.xlsx', file_get_contents($this->workbook)))
            ->set('importYear', '2019')
            ->call('checkWorkbook')
            ->assertSet('preview.title_count', 5)
            ->assertSee('awaiting data')
            ->call('importWorkbook')
            ->assertSet('preview', null);

        $this->assertSame(6, ManualAllocation::query()->count());

        $this->actingAs($officer)->get(route('manual-tracking.allocations.index'))
            ->assertOk()
            ->assertSee('Mathematics Grade 1 Student book')
            ->assertSee('TG: Mathematics Grade 1 Teachers Guide')
            ->assertSee('Amhara HPE G1')
            ->assertSee('Awaiting data');

        // An Oromia zone officer sees Oromia's allocation only, and cannot upload
        $this->actingAs($zone)->get(route('manual-tracking.allocations.index'))
            ->assertOk()
            ->assertSee('Mathematics Grade 1 Student book')
            ->assertDontSee('Amhara HPE G1')
            ->assertDontSee('Import allocation');

        Livewire::actingAs($zone)->test(AllocationIndex::class)->call('checkWorkbook')->assertForbidden();

        $this->actingAs($officer)->get(route('manual-tracking.reports.index'))
            ->assertOk()
            ->assertSee('Allocation vs delivered by region');
    }

    public function test_zone_sheets_split_the_region_allocation(): void
    {
        $header = ['Region', 'Language', 'TYPE', 'SUBJECT', 'Grade', 'Book title, grade level  and type of the book', 'Final Quantity'];
        $path = $this->writeWorkbook([
            'Oromia' => [
                $header,
                ['Oromia', 'KaffiNon Env', 'ST', 'Environmental Science', 'G1', 'KaffiNon Env Grade 1 St', 300],
                ['Oromia', 'KaffNon Moral', 'ST', 'Moral Education', 'G1', 'KaffiNon Moral Grade 1 St', 100],
            ],
            'East Shewa' => [
                ['East Shewa'],
                $header,
                ['Oromia', 'KaffiNon Env', 'ST', 'Environmental Science', 'G1', 'KaffiNon Env Grade 1 St', 200],
                ['Oromia', 'KaffNon Moral', 'ST', 'Moral Education', 'G1', 'KaffiNon Moral Grade 1 St', 60],
            ],
            'Guji' => [
                ['Guji'],
                ['Region', 'Language', 'TYPE', 'SUBJECT', 'Grade', 'Book title, grade level  and type of the book', 'Cover Color', 'Final Quantity'],
                ['Oromia', 'KaffiNon Env', 'ST', 'Environmental Science', 'G1', 'KaffiNon Env Grade 1 St', 'COLORFUL', 100],
                ['Oromia', 'KaffNon Moral', 'ST', 'Moral Education', 'G1', 'KaffiNon Moral Grade 1 St', 'COLORFUL', 30],
            ],
        ]);

        $service = app(AllocationImportService::class);
        $result = $service->parse($path, '2019');

        // The region total is not counted again for the zones, and the zone sheet's caption finds the seeded zone
        $this->assertSame(400, $result['total']);
        $this->assertSame(['Oromia / Guji'], $result['new_zones']);
        $this->assertSame(260, $result['zones']['Oromia']['East Shewa Zone']['student']);
        $this->assertStringContainsString('"KaffiNon Moral Grade 1 St" (student) is 100 on the region sheet but 90 across the zone sheets', implode("\n", $result['warnings']));

        $service->commit(null, $path, 'zonal.xlsx', '2019');

        $guji = Zone::query()->where('name', 'Guji')->firstOrFail();
        $this->assertSame((int) Region::query()->where('name', 'Oromia')->value('id'), (int) $guji->region_id);
        $this->assertTrue(ManualBook::query()->where('title', 'KaffiNon Moral Grade 1 St')->exists());
        $this->assertSame(400, (int) ManualAllocation::query()->whereNull('zone_id')->sum('quantity'));
        $this->assertSame(390, (int) ManualAllocation::query()->whereNotNull('zone_id')->sum('quantity'));
        $this->assertSame(130, (int) ManualAllocation::query()->where('zone_id', $guji->id)->sum('quantity'));

        // A regional workbook that covers Oromia replaces its region rows but leaves the zone split alone
        $service->commit(null, $this->workbook, 'allocation.xlsx', '2019');
        $this->assertSame(390, (int) ManualAllocation::query()->whereNotNull('zone_id')->sum('quantity'));
        $this->assertFalse(ManualAllocation::query()->whereNull('zone_id')->whereHas('book', fn ($book) => $book->where('title', 'like', 'KaffiNon%'))->exists());

        // An East Shewa zone officer sees the region's zones narrowed to their own
        $this->actingAs($this->user('east.shewa.zone'))->get(route('manual-tracking.allocations.index'))
            ->assertOk()
            ->assertSee('Allocation by zone')
            ->assertSee('East Shewa Zone')
            ->assertDontSee('Guji');

        $this->actingAs($this->user('moe.officer'))->get(route('manual-tracking.allocations.index', ['regionId' => $guji->region_id]))
            ->assertOk()
            ->assertSee('Guji')
            ->assertSee('East Shewa Zone');
    }

    public function test_zone_columns_split_the_region_allocation_and_fill_blank_cells(): void
    {
        $service = app(AllocationImportService::class);
        $service->commit(null, $this->workbook, 'allocation.xlsx', '2019');

        $path = $this->writeWorkbook([
            'All Regions' => [
                ['All Regions – Zonal Distribution of ETOL Printed Textbooks'],
                ['No.', 'Region', 'Number of Zones', 'Number of Books', 'Total Quantity', 'Source file'],
                [1, 'Oromia', 2, 3, 1425, 'Oromia.xlsx'],
                ['', 'TOTAL', 2, 3, 1425, ''],
            ],
            'Oromia' => [
                ['Oromia Region – Zonal Distribution of ETOL Printed Textbooks'],
                ['No.', 'Region', 'Language', 'TYPE', 'SUBJECT', 'Grade', 'Book title', 'Final Quantity', 'Shawaa Bahaa (1018)', 'Gujii (1009)', 'Remark'],
                [1, 'Oromia', 'Afaan Oromo', 'ST', 'Mathematics', '', 'Mathematics Student book', 1000, 600, 400, 'reprint'],
                [2, 'Oromia', 'Afaan Oromo', 'TG', 'Mathematics', 'G1', 'Mathematics Grade 1 Teachers Guide', 25, 25, '', ''],
                [3, 'Oromia', 'Afaan Oromo', 'ST', '', 'G1', 'Afaan Oromo FiL Grade 1 Student book', 400, '', 400, ''],
                ['', '', '', '', '', '', 'TOTAL', 1425, 625, 800, ''],
            ],
        ]);

        $result = $service->parse($path, '2019');

        // The blank grade comes from the earlier allocation, the blank subject from the title
        $this->assertSame([], $result['errors']);
        $this->assertSame(1425, $result['total']);
        $this->assertSame(1425, $result['regions']['Oromia']['expected_total']);
        $this->assertSame(['Oromia / Gujii'], $result['new_zones']);
        $this->assertSame(625, $result['zones']['Oromia']['East Shewa Zone']['student'] + $result['zones']['Oromia']['East Shewa Zone']['teacher']);
        $this->assertStringNotContainsString('Summary says', implode("\n", $result['warnings']));
        $this->assertStringNotContainsString('zone sheets', implode("\n", $result['warnings']));

        $service->commit(null, $path, 'zonal.xlsx', '2019');

        $gujii = Zone::query()->where('name', 'Gujii')->firstOrFail();
        $this->assertSame('1009', $gujii->code);
        $this->assertFalse(Zone::query()->where('name', 'like', 'Shawaa%')->exists());
        $this->assertTrue(ManualBook::query()->where('title', 'Afaan Oromo FiL Grade 1 Student book')->exists());
        $this->assertSame(1000, (int) ManualBook::query()->where('title', 'Mathematics Student book')->sole()
            ->allocations()->whereNull('zone_id')->where('edition', ManualBook::EDITION_STUDENT)->value('quantity'));
        $this->assertSame(800, (int) ManualAllocation::query()->where('zone_id', $gujii->id)->sum('quantity'));

        // Oromia's Amharic titles are no longer allocated and nothing points at them, so they go
        $this->assertFalse(ManualBook::query()->where('title', 'like', 'Am%ric%Grade 5%')->exists());

        $counts = fn () => [ManualBook::query()->whereNotNull('source_key')->count(), ManualAllocation::query()->count(), Zone::query()->count()];
        $before = $counts();
        $service->commit(null, $path, 'zonal.xlsx', '2019');
        $this->assertSame($before, $counts());
    }

    private function user(string $local): User
    {
        return User::query()->where('email', $local . '@' . ShowcaseData::EMAIL_DOMAIN)->firstOrFail();
    }

    /**
     * @param  array<string, array<int, array<int, mixed>>>  $sheets
     */
    private function writeWorkbook(array $sheets): string
    {
        $path = tempnam(sys_get_temp_dir(), 'allocation') . '.xlsx';
        $writer = new Writer();
        $writer->openToFile($path);

        $first = true;
        foreach ($sheets as $name => $rows) {
            $sheet = $first ? $writer->getCurrentSheet() : $writer->addNewSheetAndMakeItCurrent();
            $sheet->setName($name);
            $first = false;

            foreach ($rows as $row) {
                $writer->addRow(Row::fromValues($row));
            }
        }

        $writer->close();

        return $path;
    }
}
