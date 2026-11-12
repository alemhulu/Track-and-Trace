<?php

namespace Tests\Feature;

use App\Models\ManualTracking\ManualAllocation;
use App\Models\ManualTracking\ManualBook;
use App\Models\ManualTracking\ManualBookCode;
use App\Models\ManualTracking\ManualBookPackage;
use App\Models\ManualTracking\ManualScan;
use App\Models\ManualTracking\ManualWoredaAllocation;
use App\Models\Zone;
use App\Services\ManualTracking\ManualScanService;
use App\Services\ManualTracking\PrinterDataImportService;
use App\Services\ManualTracking\WoredaAllocationImportService;
use Database\Seeders\CountrySeeder;
use Database\Seeders\RegionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;
use Tests\TestCase;

/**
 * The printer's workbook per region: book editions with their QR code number and text, the
 * copies per zone, and the cartons. Student book and teacher guide of one title number are one
 * title; language editions sharing a QR text are told apart by their code number; the zone
 * allocation is the region's; cartons are grouped per zone, title and content.
 */
class PrinterDataImportTest extends TestCase
{
    use RefreshDatabase;

    private const BOOK_HEADER = ['Region', 'Region Code', 'Book QR Code No.', 'QR Code Text', 'Grade', 'Subject', 'Language', 'Edition',
        'Paired Student Book Code', 'Has Teacher Guide', 'Books per Carton', 'Title', 'ISBN', 'Print Year / Edition', 'Total Printed Copies'];

    private const ZONE_HEADER = ['Region', 'Zone Name', 'Zone Code', 'Student Book Code', 'Teacher Guide Code', 'Grade', 'Subject',
        'Student Books', 'Teacher Guides', 'Books per Carton', 'Delivery Point', 'Total Cartons'];

    private const PACKAGE_HEADER = ['Package QR Code No.', 'Package QR Text', 'Region', 'Zone Name', 'Zone Code', 'Carton Type',
        'Student Book Code', 'Student Books in Carton', 'Teacher Guide Code', 'Teacher Guides in Carton', 'Carton No.'];

    private string $workbook;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([CountrySeeder::class, RegionSeeder::class]);

        $book = fn (string $code, string $text, string $grade, string $subject, string $edition, ?string $paired, int $copies) => [
            'Oromia', '10', $code, $text, $grade, $subject, null, $edition, $paired, null, $paired ? null : 40,
            "{$subject} {$grade} {$edition}", null, 2026, $copies,
        ];

        $zone = fn (string $name, string $code, ?string $st, ?string $tg, int $students, int $guides) => [
            'Oromia', $name, $code, $st, $tg, null, null, $students, $guides, 40, "{$name} Zone Education Store", null,
        ];

        $cartons = function (string $name, string $code, string $type, ?string $st, int $students, ?string $tg, int $guides, int $count): array {
            return array_map(fn ($n) => ["ORM-Z-{$code}", "{$name} {$code}", 'Oromia', $name, $code, $type, $st, $students ?: null, $tg, $guides ?: null, $n], range(1, $count));
        };

        $this->workbook = $this->writeWorkbook([
            'Read Me' => [['Textbook Track & Trace: Printer Data Requirement']],
            '1_Books' => [
                self::BOOK_HEADER,
                $book('25110-001-2-1682', 'Oromia Level 1 Integrated Pre-Primary Student book', 'Level 1', 'Integrated Pre-Primary', 'Student book', null, 100),
                $book('25110-001-1-1683', 'Oromia Level 1 Integrated Pre-Primary Teacher guide', 'Level 1', 'Integrated Pre-Primary', 'Teacher guide', '25110-001-2-1682', 3),
                $book('25110-002-2-1684', 'Oromia Grade 1 Mathematics Student book', 'Grade 1', 'Mathematics', 'Student book', null, 81),
                $book('25110-002-1-1685', 'Oromia Grade 1 Mathematics Teacher guide', 'Grade 1', 'Mathematics', 'Teacher guide', '25110-002-2-1684', 2),
                // A second language edition of Grade 1 Mathematics: same QR text, its own code number
                $book('25110-003-2-1686', 'Oromia Grade 1 Mathematics Student book', 'Grade 1', 'Mathematics', 'Student book', null, 40),
                $book('25110-004-2-1687', 'Oromia Grade 5 Second Language(Amharic) Student book', 'Grade 5', 'Second Language(Amharic)', 'Student book', null, 40),
            ],
            '2_Zone_Allocation' => [
                self::ZONE_HEADER,
                $zone('Arsii Aanaa 28', '1001', '25110-001-2-1682', '25110-001-1-1683', 60, 2),
                $zone('Shawaa Bahaa Aanaa 13', '1018', '25110-001-2-1682', '25110-001-1-1683', 40, 1),
                $zone('Arsii Aanaa 28', '1001', '25110-002-2-1684', '25110-002-1-1685', 81, 2),
                $zone('Arsii Aanaa 28', '1001', '25110-003-2-1686', null, 40, 0),
                $zone('Arsii Aanaa 28', '1001', '25110-004-2-1687', null, 40, 0),
            ],
            '3_Package_List' => [
                self::PACKAGE_HEADER,
                ...$cartons('Arsii Aanaa 28', '1001', 'Full (40 ST + 1 TG)', '25110-001-2-1682', 40, '25110-001-1-1683', 1, 1),
                ...$cartons('Arsii Aanaa 28', '1001', 'Partial', '25110-001-2-1682', 20, '25110-001-1-1683', 1, 1),
                ...$cartons('Shawaa Bahaa Aanaa 13', '1018', 'Full (40 ST + 1 TG)', '25110-001-2-1682', 40, '25110-001-1-1683', 1, 1),
                ...$cartons('Arsii Aanaa 28', '1001', 'Full (40 ST + 1 TG)', '25110-002-2-1684', 40, '25110-002-1-1685', 1, 2),
                ...$cartons('Arsii Aanaa 28', '1001', 'Student Only Partial', '25110-002-2-1684', 1, null, 0, 1),
                ...$cartons('Arsii Aanaa 28', '1001', 'Student Only (40 ST)', '25110-003-2-1686', 40, null, 0, 1),
                ...$cartons('Arsii Aanaa 28', '1001', 'Student Only (40 ST)', '25110-004-2-1687', 40, null, 0, 1),
            ],
        ]);
    }

    public function test_books_codes_allocation_and_cartons_are_imported_and_repeatable(): void
    {
        $service = app(PrinterDataImportService::class);
        $result = $service->commit(null, $this->workbook, 'Printer_Data_Requirement-ORM.xlsx', '2019');

        $this->assertSame([], $result['errors']);
        $this->assertSame([], $result['warnings']);
        $this->assertSame(4, $result['title_count']);
        $this->assertSame(['1001', '1018'], array_map('strval', array_keys($result['zones'])));
        $this->assertSame(8, $result['cartons']);

        // Zones lose the woreda count; the aliased local name maps onto the zone already known as East Shewa
        $arsii = Zone::query()->where('code', '1001')->firstOrFail();
        $this->assertSame('Arsii', $arsii->name);
        $this->assertSame('East Shewa Zone', Zone::query()->where('code', '1018')->value('name'));

        $pre = ManualBook::query()->where('code', '25110-001')->firstOrFail();
        $this->assertSame('Integrated Pre-Primary Level 1', $pre->title);
        $this->assertSame('Level 1', $pre->grade_name);
        $this->assertSame('KG L1', $pre->grade->name);
        $this->assertTrue($pre->has_teacher_guide);
        $this->assertSame(103, $pre->total_copies);
        $this->assertSame(['student' => '25110-001-2-1682', 'teacher' => '25110-001-1-1683'], $pre->codes()->orderBy('edition')->pluck('print_code', 'edition')->all());

        // Two Grade 1 Mathematics editions: the title number keeps their titles apart
        $this->assertSame('Mathematics Grade 1 (25110-002)', ManualBook::query()->where('code', '25110-002')->value('title'));
        $this->assertSame('Mathematics Grade 1 (25110-003)', ManualBook::query()->where('code', '25110-003')->value('title'));
        $amharic = ManualBook::query()->where('code', '25110-004')->firstOrFail();
        $this->assertSame(['Amharic 2nd Language', 'Amharic', '5'], [$amharic->subject_name, $amharic->language, $amharic->grade->name]);

        // Zone rows, and the region total as their sum
        $allocated = fn (?int $zoneId, string $edition) => (int) ManualAllocation::query()->where('manual_book_id', $pre->id)
            ->where('edition', $edition)->when($zoneId, fn ($q) => $q->where('zone_id', $zoneId), fn ($q) => $q->whereNull('zone_id'))->value('quantity');
        $this->assertSame(60, $allocated($arsii->id, 'student'));
        $this->assertSame(100, $allocated(null, 'student'));
        $this->assertSame(3, $allocated(null, 'teacher'));

        // One package row per zone, title and carton content
        $full = ManualBookPackage::query()->where('zone_id', $arsii->id)->where('manual_book_id', ManualBook::query()->where('code', '25110-002')->value('id'))
            ->where('carton_type', 'Full (40 ST + 1 TG)')->firstOrFail();
        $this->assertSame(['ORM-Z-1001/25110-002/F40+1', 2, 40, 1, 80, 2, 'ORM-Z-1001', 'Arsii Aanaa 28 1001'],
            [$full->package_code, $full->no_of_packages, $full->books_per_package, $full->teacher_guides_per_package, $full->total_books, $full->total_teacher_guides, $full->package_qr_code, $full->package_qr_text]);
        $this->assertSame(7, ManualBookPackage::query()->count());

        $counts = fn () => [ManualBook::query()->count(), ManualBookCode::query()->count(), ManualAllocation::query()->count(), (int) ManualAllocation::query()->sum('quantity'),
            ManualBookPackage::query()->count(), (int) ManualBookPackage::query()->sum('no_of_packages'), Zone::query()->count()];
        $before = $counts();
        $service->commit(null, $this->workbook, 'Printer_Data_Requirement-ORM.xlsx', '2019');
        $this->assertSame($before, $counts());
    }

    public function test_scans_tell_shared_qr_texts_apart_and_read_zone_codes(): void
    {
        app(PrinterDataImportService::class)->commit(null, $this->workbook, 'printer.xlsx', '2019');
        $scan = app(ManualScanService::class);

        $byNumber = $scan->identify('25110-003-2-1686');
        $this->assertSame('25110-003', $byNumber['book']->code);
        $this->assertNull($byNumber['flag']);

        $shared = $scan->identify('oromia grade 1  MATHEMATICS student book');
        $this->assertSame(ManualScan::FLAG_AMBIGUOUS_BOOK, $shared['flag']);
        $this->assertNull($shared['book']);
        $this->assertNull(ManualBookCode::findByScan('Oromia Grade 1 Mathematics Student book'));

        $this->assertSame('25110-002', $scan->identify('Oromia Grade 1 Mathematics Teacher guide')['book']->code);

        foreach (['Arsii Aanaa 28 1001', 'ORM-Z-1001', 'Arsii'] as $label) {
            $this->assertSame('Arsii', $scan->identify($label)['zone']?->name, $label);
        }
    }

    public function test_oromia_woreda_split_follows_the_printer_titles(): void
    {
        app(PrinterDataImportService::class)->commit(null, $this->workbook, 'printer.xlsx', '2019');

        $woredaBook = $this->writeWorkbook([
            'Pre-Primary' => [
                ['Oromia Region Education Bureau student Text book & Teachers Guide  Distribution 2019/2026'],
                ['zone', 'woreda', 'Pre-Primary Practs Book & TG Level I & II Distribution '],
                ['', '', 'Level 5 (I)', '', 'Level 6 (II)', '', 'Total.'],
                ['', '', 'Practs Book', 'TG', 'Practs Book', 'TG', 'Practs Book', 'TG', 'G.Total'],
                ['Arsii  Aanaa 28', 'Amiinyaa', 30.4, 1.2, 10, 0],
                ['', 'Asakoo', 29.6, 0.8, 10, 0],
                ['Shawaa Bahaa Aanaa 13', 'Adaa', 40, 1, 10, 0],
            ],
        ]);

        $result = app(WoredaAllocationImportService::class)->commit(null, $woredaBook, 'Oromia woreda.xlsx', 'Oromia', '2019');

        $this->assertSame([], $result['errors']);
        $this->assertSame(['Grade 6'], $result['sheets']['Pre-Primary']['missing']);
        $pre = ManualBook::query()->where('code', '25110-001')->firstOrFail();
        $this->assertSame(60, (int) ManualWoredaAllocation::query()->where('manual_book_id', $pre->id)->where('zone_id', Zone::query()->where('code', '1001')->value('id'))->where('edition', 'student')->sum('quantity'));
        $this->assertSame(100, (int) ManualWoredaAllocation::query()->where('manual_book_id', $pre->id)->where('edition', 'student')->sum('quantity'));
        $this->assertSame(3, (int) ManualWoredaAllocation::query()->where('manual_book_id', $pre->id)->where('edition', 'teacher')->sum('quantity'));
    }

    public function test_unknown_book_codes_stop_the_import(): void
    {
        $path = $this->writeWorkbook([
            '1_Books' => [self::BOOK_HEADER, ['Oromia', '10', '25110-001-2-1682', 'Oromia Grade 1 English Student book', 'Grade 1', 'English', null, 'Student book', null, 'NO', 40, 'English Grade 1 Student book', null, 2026, 40]],
            '2_Zone_Allocation' => [self::ZONE_HEADER, ['Oromia', 'Arsii Aanaa 28', '1001', '25110-099-2-0001', null, null, null, 40, 0, 40, null, 1]],
            '3_Package_List' => [self::PACKAGE_HEADER],
        ]);

        $result = app(PrinterDataImportService::class)->commit(null, $path, 'bad.xlsx', '2019');

        $this->assertNull($result['import']);
        $this->assertSame(['2_Zone_Allocation row 2: book code 25110-099-2-0001 is not in 1_Books.'], $result['errors']);
        $this->assertSame(0, ManualBook::query()->count());
    }

    /**
     * @param  array<string, array<int, array<int, mixed>>>  $sheets
     */
    private function writeWorkbook(array $sheets): string
    {
        $path = tempnam(sys_get_temp_dir(), 'printer') . '.xlsx';
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
