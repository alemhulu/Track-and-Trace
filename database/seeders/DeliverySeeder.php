<?php

namespace Database\Seeders;

use App\Models\delivery as DeliveryModel;
use App\Models\Package;
use Illuminate\Database\Seeder;

class DeliverySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $packages = Package::query()
            ->with('printOrder.book')
            ->where('step', 4)
            ->orderBy('id')
            ->take(25)
            ->get();

        foreach ($packages as $index => $package) {
            $book = optional($package->printOrder)->book;
            $quantity = (int) ($package->received ?: $package->sent ?: $package->no_of_books ?: 0);

            $booksPayload = [
                [
                    'book_id' => $book?->id,
                    'isbn' => $book?->isbn,
                    'quantity' => $quantity,
                ],
            ];

            $studentId = 'STU-' . str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT);

            DeliveryModel::updateOrCreate(
                ['student_id' => $studentId],
                [
                    'name' => 'Student Group ' . ($index + 1),
                    'books' => json_encode($booksPayload),
                    'distributed' => (int) ((bool) $package->delivery_status),
                ]
            );
        }
    }
}
