<?php

namespace Tests\Feature;

use App\Models\ManualTracking\ManualDistribution;
use App\Models\Organization;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\Showcase\ShowcaseData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Every manual tracking page and dashboard renders against realistic data for users at
 * different levels.
 */
class ManualTrackingPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_pages_render_for_national_zone_and_school_users(): void
    {
        Storage::fake('public');
        $_ENV['SEED_PROFILE'] = 'showcase';
        $this->seed(DatabaseSeeder::class);
        unset($_ENV['SEED_PROFILE']);

        $shipment = ManualDistribution::query()->where('status', ManualDistribution::STATUS_DISPATCHED)->firstOrFail();
        $pages = [
            route('manual-tracking.books.list'),
            route('manual-tracking.stock.index'),
            route('manual-tracking.distribution.list'),
            route('manual-tracking.discrepancies.index'),
            route('manual-tracking.scan.alerts'),
            route('manual-tracking.visits.index'),
            route('field.app'),
            route('manual-tracking.reports.index'),
            route('manual-tracking.schools.index'),
            route('dashboard'),
            route('dashboard.inventory'),
            route('dashboard.distribution'),
        ];

        foreach (['moe.officer', 'east.shewa.zone', 'director.ifa.bula.primary'] as $local) {
            $user = User::query()->where('email', $local . '@' . ShowcaseData::EMAIL_DOMAIN)->firstOrFail();

            foreach ($pages as $page) {
                $this->actingAs($user)->get($page)->assertOk();
            }

            // Titles are registered by Admin and Super-Admin only
            $this->actingAs($user)->get(route('manual-tracking.books.add'))->assertForbidden();

            // Schools only receive, so they get neither the shipment form nor the sorting bay
            $sends = ! $user->isSchoolUser();
            $this->actingAs($user)->get(route('manual-tracking.distribution.add'))->assertStatus($sends ? 200 : 403);
            $this->actingAs($user)->get(route('manual-tracking.scan.sort'))->assertStatus($sends ? 200 : 403);
        }

        $this->actingAs(User::query()->where('email', 'moe.officer@' . ShowcaseData::EMAIL_DOMAIN)->firstOrFail())
            ->get(route('manual-tracking.shipments.show', $shipment->id))
            ->assertOk()
            ->assertSee($shipment->reference);

        $this->actingAs(User::query()->where('email', 'moe.officer@' . ShowcaseData::EMAIL_DOMAIN)->firstOrFail())
            ->get(route('manual-tracking.scan.alerts'))
            ->assertOk()
            ->assertSee('Package for');

        $officer = User::query()->where('email', 'moe.officer@' . ShowcaseData::EMAIL_DOMAIN)->firstOrFail();
        $visit = \App\Models\Oversight\VerificationVisit::query()->firstOrFail();
        $this->actingAs($officer)->get(route('manual-tracking.visits.show', $visit->id))->assertOk()->assertSee($visit->school->name);
        $this->actingAs($officer)->get(route('dashboard'))->assertSee('Reaching students')->assertSee('Schools needing attention');
        foreach (array_keys(\App\Http\Controllers\ManualTracking\ManualExportController::TYPES) as $type) {
            $this->actingAs($officer)->get(route('manual-tracking.exports', $type))->assertOk();
        }

        // A school's own records open for its director and are hidden from other schools
        $school = Organization::query()->where('name', 'Ifa Bula Primary School')->firstOrFail();
        $director = User::query()->where('email', 'director.ifa.bula.primary@' . ShowcaseData::EMAIL_DOMAIN)->firstOrFail();
        $this->actingAs($director)->get(route('manual-tracking.schools.show', $school->id))
            ->assertOk()
            ->assertSee('Oromia Grade 6 Mathematics')
            ->assertSee('Issue Books');

        $otherDirector = User::query()->where('email', 'director.bilisuma.primary@' . ShowcaseData::EMAIL_DOMAIN)->firstOrFail();
        $this->actingAs($otherDirector)->get(route('manual-tracking.schools.show', $school->id))->assertNotFound();
    }
}
