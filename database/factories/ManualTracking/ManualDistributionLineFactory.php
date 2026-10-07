<?php

namespace Database\Factories\ManualTracking;

use App\Models\ManualTracking\ManualBook;
use App\Models\ManualTracking\ManualBookPackage;
use App\Models\ManualTracking\ManualDistribution;
use App\Models\ManualTracking\ManualDistributionLine;
use Illuminate\Database\Eloquent\Factories\Factory;

class ManualDistributionLineFactory extends Factory
{
    protected $model = ManualDistributionLine::class;

    public function definition(): array
    {
        $distribution = ManualDistribution::query()->inRandomOrder()->first() ?? ManualDistribution::factory()->create();
        $book = ManualBook::query()->inRandomOrder()->first() ?? ManualBook::factory()->create();
        $package = ManualBookPackage::query()->where('manual_book_id', $book->id)->inRandomOrder()->first()
            ?? ManualBookPackage::factory()->create(['manual_book_id' => $book->id]);

        $sourceBefore = max(0, (int) ($package?->current_balance ?? $package?->total_books ?? $book->total_copies));
        $quantity = $sourceBefore > 0 ? $this->faker->numberBetween(1, $sourceBefore) : 0;

        return [
            'manual_distribution_id' => $distribution->id,
            'manual_book_id' => $book->id,
            'manual_book_package_id' => $package?->id,
            'quantity' => $quantity,
            'source_balance_before' => $sourceBefore,
            'source_balance_after' => max($sourceBefore - $quantity, 0),
        ];
    }
}
