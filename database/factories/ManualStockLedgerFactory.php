<?php

namespace Database\Factories;

use App\Models\ManualTracking\ManualBook;
use App\Models\ManualTracking\ManualBookPackage;
use App\Models\ManualTracking\ManualStockLedger;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ManualStockLedgerFactory extends Factory
{
    protected $model = ManualStockLedger::class;

    public function definition(): array
    {
        $book = ManualBook::query()->inRandomOrder()->first() ?? ManualBook::factory()->create();
        $package = ManualBookPackage::query()->where('manual_book_id', $book->id)->inRandomOrder()->first()
            ?? ManualBookPackage::factory()->create(['manual_book_id' => $book->id]);
        $organization = Organization::query()->inRandomOrder()->first();
        $user = User::query()->inRandomOrder()->first();
        $movementQty = $this->faker->numberBetween(-200, 250);

        return [
            'manual_book_id' => $book->id,
            'manual_book_package_id' => $package?->id,
            'movement_type' => $this->faker->randomElement(['initial', 'inbound', 'outbound', 'adjustment']),
            'movement_qty' => $movementQty,
            'balance_after' => max(0, (int) ($package?->current_balance ?? 0) + $movementQty),
            'ref_type' => 'manual_tracking',
            'ref_id' => $this->faker->randomDigitNotZero(),
            'acted_by' => $user?->id,
            'organization_id' => $organization?->id,
            'country_id' => $organization?->country_id,
            'region_id' => $organization?->region_id,
            'zone_id' => $organization?->zone_id,
            'woreda_id' => $organization?->woreda_id,
            'notes' => $this->faker->sentence(6),
        ];
    }
}
