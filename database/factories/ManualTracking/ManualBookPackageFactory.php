<?php

namespace Database\Factories\ManualTracking;

use App\Models\ManualTracking\ManualBook;
use App\Models\ManualTracking\ManualBookPackage;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ManualBookPackageFactory extends Factory
{
    protected $model = ManualBookPackage::class;

    public function definition(): array
    {
        $status = $this->faker->randomElement(['Packed', 'In Transit', 'Distributed']);
        $booksPerPackage = 40;
        $totalBooks = $booksPerPackage;
        $currentBalance = match ($status) {
            'Distributed' => 0,
            default => $totalBooks,
        };

        return [
            'manual_book_id' => ManualBook::factory(),
            'package_code' => 'TRK-' . strtoupper(Str::random(4)) . '-' . $this->faker->unique()->numberBetween(100000, 999999),
            'no_of_packages' => 1,
            'books_per_package' => $booksPerPackage,
            'total_books' => $totalBooks,
            'current_balance' => $currentBalance,
            'status' => $status,
            'notes' => $this->faker->sentence(6),
        ];
    }
}
