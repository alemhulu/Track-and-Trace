<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\Organization;
use App\Models\PrintOrder;
use Illuminate\Database\Eloquent\Factories\Factory;

class PrintOrderFactory extends Factory
{
    protected $model = PrintOrder::class;

    public function definition()
    {
        $noOfBooks = $this->faker->numberBetween(1000, 15000);
        $bookPerPackage = $this->faker->numberBetween(10, 30);
        $noOfPackages = (int) ceil($noOfBooks / $bookPerPackage);

        $barcodeStart = $this->faker->numberBetween(100000000000, 100000900000);
        $barcodeEnd = $barcodeStart + $noOfBooks - 1;

        $qrcodeStart = $this->faker->numberBetween(900000000000, 900000900000);
        $qrcodeEnd = $qrcodeStart + $noOfBooks - 1;

        return [
            'order_organization_id' => Organization::factory(),
            'printer_organization_id' => Organization::factory(),
            'book_id' => Book::factory(),
            'no_of_books' => $noOfBooks,
            'no_of_packages' => $noOfPackages,
            'Book_codes' => [
                [
                    'batch' => 'FAC-' . $this->faker->numerify('###'),
                    'barcode_start' => $barcodeStart,
                    'barcode_end' => $barcodeEnd,
                    'qrcode_start' => $qrcodeStart,
                    'qrcode_end' => $qrcodeEnd,
                ],
            ],
            'expected_print_time' => now()->addDays(10)->toDateString(),
            'actual_print_time' => null,
            'print_status' => 1,
            'request_status' => 1,
            'barcode_start' => $barcodeStart,
            'barcode_end' => $barcodeEnd,
            'qrcode_start' => $qrcodeStart,
            'qrcode_end' => $qrcodeEnd,
            'book_per_package' => $bookPerPackage,
        ];
    }
}
