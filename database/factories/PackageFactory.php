<?php

namespace Database\Factories;

use App\Models\Organization;
use App\Models\Package;
use App\Models\PrintOrder;
use App\Models\WareHouse;
use Illuminate\Database\Eloquent\Factories\Factory;

class PackageFactory extends Factory
{
    protected $model = Package::class;

    public function definition()
    {
        return [
            'ware_house_id' => WareHouse::factory(),
            'print_order_id' => PrintOrder::factory(),
            'sender_organization_id' => Organization::factory(),
            'receiver_organization_id' => Organization::factory(),
            'step' => $this->faker->numberBetween(1, 4),
            'Book_codes' => [
                [
                    'batch' => 'PKG-' . $this->faker->numerify('###'),
                    'count' => $this->faker->numberBetween(200, 1500),
                ],
            ],
            'received' => 100,
            'sent' => 100,
            'balance' => 0,
            'no_of_books' => 100,
            'books_per_package' => 25,
            'qrcode_start' => $this->faker->numberBetween(900000000000, 900000900000),
            'qrcode_end' => $this->faker->numberBetween(900000900001, 900001800000),
            'barcode_start' => $this->faker->numberBetween(100000000000, 100000900000),
            'barcode_end' => $this->faker->numberBetween(100000900001, 100001800000),
            'expected_send_time' => now()->addDay()->toDateString(),
            'actual_send_time' => now()->toDateString(),
            'expected_delivery_school_time' => now()->addDays(2)->toDateString(),
            'actual_delivery_school_time' => now()->addDays(2)->toDateString(),
            'request_status' => true,
            'delivery_status' => false,
            'description' => 'Factory package payload',
            'subject_id' => function (array $attributes) {
                $book = optional(PrintOrder::query()->find($attributes['print_order_id'] ?? null))->book;
                return $book?->subject_id;
            },
            'grade_id' => function (array $attributes) {
                $book = optional(PrintOrder::query()->find($attributes['print_order_id'] ?? null))->book;
                return $book?->grade_id;
            },
        ];
    }
}
