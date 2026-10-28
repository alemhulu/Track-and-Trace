<?php

namespace Tests\Feature;

use App\Http\Livewire\Users\LocationCascade;
use App\Models\Country;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LocationCascadeDefaultCountryTest extends TestCase
{
    use RefreshDatabase;

    public function test_location_cascade_defaults_to_ethiopia_country(): void
    {
        $country = Country::query()->firstOrCreate(
            ['name' => 'Ethiopia'],
            ['code' => 'ET']
        );

        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(LocationCascade::class, ['mode' => 'create'])
            ->assertSet('countryId', $country->id);
    }
}
