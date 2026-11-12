<?php

namespace Tests\Feature;

use App\Http\Livewire\ManualTracking\Allocation\AllocationIndex;
use App\Models\ManualTracking\ManualAllocationImport;
use App\Models\ManualTracking\ManualBook;
use App\Models\ManualTracking\ManualWoredaAllocation;
use App\Models\User;
use App\Models\Woreda;
use App\Models\Zone;
use App\Services\ManualTracking\AllocationImportService;
use App\Services\ManualTracking\WoredaAllocationImportService;
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
 * A regional bureau's woreda split: a sheet per subject with the zone on its first woreda,
 * subtotal rows skipped, sheets matched to the allocated titles by subject and grade, the
 * fractional shares rounded onto each zone's allocation, and woredas created under their zone.
 */
class WoredaAllocationImportTest extends TestCase
{
    use RefreshDatabase;

    private string $woredaBook;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $_ENV['SEED_PROFILE'] = 'showcase';
        $this->seed(DatabaseSeeder::class);
        unset($_ENV['SEED_PROFILE']);

        // The Ministry allocation the split is measured against: Oromia, and its East Shewa and Arsii shares
        $header = ['Region', 'Language', 'TYPE', 'SUBJECT', 'Grade', 'Book title, grade level  and type of the book', 'Final Quantity'];
        $rows = fn (int $math, int $guide, int $pre) => [
            $header,
            ['Oromia', 'Afaan Oromo', 'ST', 'Mathematics', 'G1', 'Mathes Grade 1Student book', $math],
            ['Oromia', 'Afaan Oromo', 'TG', 'Mathematics', 'G1', 'Mathes Grade 1 Teacher Guide', $guide],
            ['Oromia', 'Afaan Oromo', 'ST', 'Integrated Pre-Primary', 'G5', 'Preprimery level I (Age 5)Student Practice book', $pre],
        ];
        app(AllocationImportService::class)->commit(null, $this->writeWorkbook([
            'Oromia' => $rows(1000, 25, 500),
            'East Shewa' => array_merge([['East Shewa']], $rows(600, 15, 300)),
            'Arsii' => array_merge([['Arsii']], $rows(400, 10, 200)),
        ]), 'ministry.xlsx', '2019');

        $this->woredaBook = $this->writeWorkbook([
            'Mathes G. 1-6. Distribution' => [
                ['Oromia Region Education Bureau student Text book & Teachers Guide  Distribution 2019/2026'],
                ['Qooddii Kitaaba Barnoota Herreegaa Kutaa 1-6 '],
                ['zone', 'woreda', 1, '', 2, '', 'W/G'],
                ['', '', 'STB', 'TG', 'STB', 'TG', 'STB', 'TG', 'Total'],
                ['Shawaa Bahaa Aanaa 2', 'Adaa ', 299.6, 7.4, 50, 2, 349.6, 9.4],
                ['', 'Lume', 300.7, 7.7, 50, 2, 350.7, 9.7],
                ['', 'Idaáma', 600.3, 15.1, 100, 4, 700.3, 19.1],
                ['Arsii  Aanaa 1', 'B/M/ Asallaa', 400.4, 9.8, 80, 3, 480.4, 12.8],
                ['', 'Idaáma', 400.4, 9.8, 80, 3, 480.4, 12.8],
                ['Grand Total', '', 1000.7, 24.9, 180, 7, 1180.7, 31.9],
            ],
            'Pre-Primary' => [
                ['Oromia Region Education Bureau student Text book & Teachers Guide  Distribution 2019/2026'],
                ['zone', 'woreda', 'Pre-Primary Practs Book & TG Level I & II Distribution '],
                ['', '', 'Level 5 (I)', '', 'Level 6 (II)', '', 'Total.'],
                ['', '', 'Practs Book', 'TG', 'Practs Book', 'TG', 'Practs Book', 'TG', 'G.Total'],
                ['Shawaa Bahaa', 'Adaa', 150.5, 1, 140, 1],
                ['Shawaa Bahaa', 'Lume', 149.5, 1, 140, 1],
                ['B/M/Asallaa', 'B/M/Asallaa', 200, 1, 190, 1],
                ['Idaáma', ''],
            ],
        ]);
    }

    public function test_split_is_matched_rounded_onto_the_zone_and_repeatable(): void
    {
        $service = app(WoredaAllocationImportService::class);
        $result = $service->parse($this->woredaBook, 'Oromia', '2019');

        $this->assertSame([], $result['errors']);
        $this->assertSame('Mathematics', $result['sheets']['Mathes G. 1-6. Distribution']['subject']);
        $this->assertSame(['Grade 2'], $result['sheets']['Mathes G. 1-6. Distribution']['missing']);
        $this->assertSame(['Grade 6'], $result['sheets']['Pre-Primary']['missing']);
        $this->assertSame(3, $result['woreda_count']);
        // The workbook's 600.3 and 400.4 are rounded onto the zones' 600 and 400
        $this->assertSame(600 + 300, $result['zones']['East Shewa Zone']['student']);
        $this->assertSame(400 + 200, $result['zones']['Arsii']['student']);

        $import = $service->commit(null, $this->woredaBook, 'woreda.xlsx', 'Oromia', '2019')['import'];
        $this->assertSame('woreda', $import->level);

        $eastShewa = Zone::query()->where('name', 'East Shewa Zone')->firstOrFail();
        $arsii = Zone::query()->where('name', 'Arsii')->firstOrFail();
        $adaa = Woreda::query()->where('name', 'Adaa')->firstOrFail();
        $asallaa = Woreda::query()->where('zone_id', $arsii->id)->firstOrFail();
        $math = ManualBook::query()->where('title', 'Mathes Grade 1Student book')->firstOrFail();

        $this->assertSame($eastShewa->id, (int) $adaa->zone_id);
        $this->assertSame('B/M/ Asallaa', $asallaa->name);
        $this->assertSame(600, (int) ManualWoredaAllocation::query()->where('zone_id', $eastShewa->id)->where('manual_book_id', $math->id)->where('edition', 'student')->sum('quantity'));
        $this->assertSame(15, (int) ManualWoredaAllocation::query()->where('zone_id', $eastShewa->id)->where('manual_book_id', $math->id)->where('edition', 'teacher')->sum('quantity'));
        // 299.6 and 300.7 scaled onto 600 are 299.45 and 300.55; the spare copy goes to the larger remainder
        $this->assertSame(299, (int) ManualWoredaAllocation::query()->where('woreda_id', $adaa->id)->where('manual_book_id', $math->id)->where('edition', 'student')->value('quantity'));
        // The Pre-Primary sheet names towns in its zone column; the woreda is found under Arsii from the maths sheet
        $this->assertSame(200, (int) ManualWoredaAllocation::query()->where('woreda_id', $asallaa->id)->where('edition', 'student')->whereHas('book', fn ($book) => $book->where('subject_name', 'Integrated Pre-Primary'))->sum('quantity'));

        $counts = fn () => [Woreda::query()->count(), ManualWoredaAllocation::query()->count(), (int) ManualWoredaAllocation::query()->sum('quantity')];
        $before = $counts();
        $service->commit(null, $this->woredaBook, 'woreda.xlsx', 'Oromia', '2019');
        $this->assertSame($before, $counts());
    }

    public function test_zones_named_as_in_the_workbook_and_town_named_zones_are_handled(): void
    {
        // A database that names East Shewa as the bureau does, and has a zone named after a town
        // the allocation does not cover
        $eastShewa = Zone::query()->where('name', 'East Shewa Zone')->firstOrFail();
        $eastShewa->update(['name' => 'Shawaa Bahaa']);
        $town = Zone::query()->create(['name' => 'B/M/Asallaa', 'region_id' => $eastShewa->region_id, 'country_id' => $eastShewa->country_id]);

        $service = app(WoredaAllocationImportService::class);
        $result = $service->parse($this->woredaBook, 'Oromia', '2019');

        $this->assertSame([], $result['errors']);
        $this->assertSame(600 + 300, $result['zones']['Shawaa Bahaa']['student']);
        $this->assertSame(400 + 200, $result['zones']['Arsii']['student']);
        $this->assertArrayNotHasKey('B/M/Asallaa', $result['zones']);

        $service->commit(null, $this->woredaBook, 'woreda.xlsx', 'Oromia', '2019');
        $this->assertSame(0, ManualWoredaAllocation::query()->where('zone_id', $town->id)->count());
    }

    public function test_largest_remainder_rounding_keeps_the_total(): void
    {
        $rounded = app(WoredaAllocationImportService::class)->roundToTotal(['a' => 0.4, 'b' => 0.4, 'c' => 0.2], 1);
        $this->assertSame(1, array_sum($rounded));

        $rounded = app(WoredaAllocationImportService::class)->roundToTotal(['a' => 33.3, 'b' => 33.3, 'c' => 33.4], 100);
        $this->assertSame(['a' => 33, 'b' => 33, 'c' => 34], $rounded);
    }

    public function test_officer_uploads_the_split_and_sees_it_per_woreda(): void
    {
        $officer = $this->user('moe.officer');

        Livewire::actingAs($officer)->test(AllocationIndex::class)
            ->set('importLevel', 'woreda')
            ->set('importRegion', 'Oromia')
            ->set('workbook', UploadedFile::fake()->createWithContent('woreda.xlsx', file_get_contents($this->woredaBook)))
            ->set('importYear', '2019')
            ->call('checkWorkbook')
            ->assertSet('woredaPreview.woreda_count', 3)
            ->assertSee('Regional woreda split')
            ->call('importWorkbook')
            ->assertSet('woredaPreview', null);

        $this->assertSame(1, ManualAllocationImport::query()->where('level', 'woreda')->count());

        $eastShewa = Zone::query()->where('name', 'East Shewa Zone')->firstOrFail();
        $adaa = Woreda::query()->where('name', 'Adaa')->firstOrFail();

        $this->actingAs($officer)->get(route('manual-tracking.allocations.index', ['regionId' => $eastShewa->region_id, 'zoneId' => $eastShewa->id]))
            ->assertOk()
            ->assertSee('Allocation by woreda')
            ->assertSee('Adaa')
            ->assertSee('Lume');

        $this->actingAs($officer)->get(route('manual-tracking.allocations.index', ['regionId' => $eastShewa->region_id, 'zoneId' => $eastShewa->id, 'woredaId' => $adaa->id]))
            ->assertOk()
            ->assertSee('Allocation by title · Adaa')
            ->assertSee('Mathes Grade 1Student book');

        // A zone officer only sees their own zone's woredas
        $this->actingAs($this->user('east.shewa.zone'))->get(route('manual-tracking.allocations.index'))
            ->assertOk()
            ->assertSee('Adaa')
            ->assertDontSee('B/M/ Asallaa');
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
        $path = tempnam(sys_get_temp_dir(), 'woreda') . '.xlsx';
        $writer = new Writer();
        $writer->openToFile($path);

        $first = true;
        foreach ($sheets as $name => $rows) {
            $sheet = $first ? $writer->getCurrentSheet() : $writer->addNewSheetAndMakeItCurrent();
            $sheet->setName(substr($name, 0, 31));
            $first = false;

            foreach ($rows as $row) {
                $writer->addRow(Row::fromValues($row));
            }
        }

        $writer->close();

        return $path;
    }
}
