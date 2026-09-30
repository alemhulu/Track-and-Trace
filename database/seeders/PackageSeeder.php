<?php

namespace Database\Seeders;

use App\Models\Package;
use App\Models\PrintOrder;
use App\Models\WareHouse;
use Illuminate\Database\Seeder;

class PackageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $printerWarehouse = WareHouse::query()->where('branch', 1101)->first();
        $moeWarehouse = WareHouse::query()->where('branch', 1102)->first();
        $regionalWarehouse = WareHouse::query()->where('branch', 1103)->first();
        $zoneWarehouse = WareHouse::query()->where('branch', 1104)->first();
        $schoolWarehouse = WareHouse::query()->where('branch', 1105)->first();

        if (! $printerWarehouse || ! $moeWarehouse || ! $regionalWarehouse || ! $zoneWarehouse || ! $schoolWarehouse) {
            return;
        }

        $printOrders = PrintOrder::query()->with('book')->orderBy('id')->get();

        foreach ($printOrders as $orderIndex => $printOrder) {
            $book = $printOrder->book;
            if (! $book || ! $book->grade_id || ! $book->subject_id) {
                continue;
            }

            $totalBooks = (int) ($printOrder->no_of_books ?? 0);
            $perStepBooks = max(200, (int) floor($totalBooks / 3));
            $bookPerPackage = (int) ($printOrder->book_per_package ?? 25);

            $legs = [
                [
                    'step' => 1,
                    'warehouse' => $printerWarehouse,
                    'sender_org' => $printerWarehouse->organization_id,
                    'receiver_org' => $moeWarehouse->organization_id,
                    'delivery_status' => false,
                ],
                [
                    'step' => 2,
                    'warehouse' => $moeWarehouse,
                    'sender_org' => $moeWarehouse->organization_id,
                    'receiver_org' => $regionalWarehouse->organization_id,
                    'delivery_status' => false,
                ],
                [
                    'step' => 3,
                    'warehouse' => $regionalWarehouse,
                    'sender_org' => $regionalWarehouse->organization_id,
                    'receiver_org' => $zoneWarehouse->organization_id,
                    'delivery_status' => false,
                ],
                [
                    'step' => 4,
                    'warehouse' => $zoneWarehouse,
                    'sender_org' => $zoneWarehouse->organization_id,
                    'receiver_org' => $schoolWarehouse->organization_id,
                    'delivery_status' => true,
                ],
            ];

            foreach ($legs as $legIndex => $leg) {
                $barcodeStart = (int) ($printOrder->barcode_start ?? 100000000000) + (($legIndex + 1) * 1000) + ($orderIndex * 10000);
                $barcodeEnd = $barcodeStart + max(1, $perStepBooks - 1);
                $qrcodeStart = (int) ($printOrder->qrcode_start ?? 900000000000) + (($legIndex + 1) * 1000) + ($orderIndex * 10000);
                $qrcodeEnd = $qrcodeStart + max(1, $perStepBooks - 1);

                Package::updateOrCreate(
                    [
                        'print_order_id' => $printOrder->id,
                        'step' => $leg['step'],
                        'ware_house_id' => $leg['warehouse']->id,
                        'sender_organization_id' => $leg['sender_org'],
                        'receiver_organization_id' => $leg['receiver_org'],
                    ],
                    [
                        'Book_codes' => [
                            [
                                'book_id' => $book->id,
                                'isbn' => $book->isbn,
                                'count' => $perStepBooks,
                            ],
                        ],
                        'received' => $perStepBooks,
                        'sent' => $perStepBooks,
                        'balance' => 0,
                        'no_of_books' => $perStepBooks,
                        'books_per_package' => $bookPerPackage,
                        'qrcode_start' => $qrcodeStart,
                        'qrcode_end' => $qrcodeEnd,
                        'barcode_start' => $barcodeStart,
                        'barcode_end' => $barcodeEnd,
                        'expected_send_time' => now()->addDays($leg['step'])->toDateString(),
                        'actual_send_time' => now()->addDays($leg['step'])->toDateString(),
                        'expected_delivery_school_time' => now()->addDays($leg['step'] + 1)->toDateString(),
                        'actual_delivery_school_time' => $leg['delivery_status'] ? now()->addDays($leg['step'] + 1)->toDateString() : null,
                        'request_status' => true,
                        'delivery_status' => $leg['delivery_status'],
                        'description' => 'Demo package movement for seeded trace flow.',
                        'subject_id' => $book->subject_id,
                        'grade_id' => $book->grade_id,
                    ]
                );
            }
        }
    }
}
