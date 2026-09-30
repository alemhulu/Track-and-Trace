<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Organization;
use App\Models\PrintOrder;
use Illuminate\Database\Seeder;

class PrintOrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $orderOrganization = Organization::query()->where('name', 'Federal Ministry of Education')->first();
        $printerOrganization = Organization::query()->where('name', 'National School Printer')->first();

        if (! $orderOrganization || ! $printerOrganization) {
            return;
        }

        $books = Book::query()->orderBy('id')->get();
        $barcodeBase = 100000000000;
        $qrcodeBase = 900000000000;

        foreach ($books as $index => $book) {
            $noOfBooks = 5000 + (($index + 1) * 750);
            $bookPerPackage = 25;
            $noOfPackages = (int) ceil($noOfBooks / $bookPerPackage);

            $barcodeStart = $barcodeBase + ($index * 10000);
            $barcodeEnd = $barcodeStart + $noOfBooks - 1;
            $qrcodeStart = $qrcodeBase + ($index * 10000);
            $qrcodeEnd = $qrcodeStart + $noOfBooks - 1;

            PrintOrder::updateOrCreate(
                [
                    'book_id' => $book->id,
                    'order_organization_id' => $orderOrganization->id,
                    'printer_organization_id' => $printerOrganization->id,
                ],
                [
                    'no_of_books' => $noOfBooks,
                    'no_of_packages' => $noOfPackages,
                    'Book_codes' => [
                        [
                            'batch' => 'BATCH-' . str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT),
                            'barcode_start' => $barcodeStart,
                            'barcode_end' => $barcodeEnd,
                            'qrcode_start' => $qrcodeStart,
                            'qrcode_end' => $qrcodeEnd,
                        ],
                    ],
                    'expected_print_time' => now()->addDays(14)->toDateString(),
                    'actual_print_time' => null,
                    'print_status' => 1,
                    'request_status' => 1,
                    'description' => 'Demo print order for trace and distribution workflow validation.',
                    'qrcode_start' => $qrcodeStart,
                    'qrcode_end' => $qrcodeEnd,
                    'barcode_start' => $barcodeStart,
                    'barcode_end' => $barcodeEnd,
                    'book_per_package' => $bookPerPackage,
                ]
            );
        }
    }
}
