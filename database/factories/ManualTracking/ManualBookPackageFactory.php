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
        $booksPerPackage = $this->faker->numberBetween(20, 150);
        $noOfPackages = $this->faker->numberBetween(1, 6);
        $totalBooks = $noOfPackages * $booksPerPackage;

        return [
            'manual_book_id' => ManualBook::factory(),
            'package_code' => 'PKG-' . strtoupper(Str::random(3)) . '-' . $this->faker->unique()->numberBetween(1000, 9999),
            'no_of_packages' => $noOfPackages,
            'books_per_package' => $booksPerPackage,
            'total_books' => $totalBooks,
            'current_balance' => $totalBooks,
            'status' => $this->faker->randomElement(['available', 'partial', 'issued']),
            'notes' => $this->faker->sentence(6),
        ];
    }
}
