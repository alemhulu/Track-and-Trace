<?php

namespace Tests\Feature;

use App\Http\Livewire\ManualTracking\Book\AddManualBook;
use App\Http\Livewire\ManualTracking\Book\ListManualBook;
use App\Http\Livewire\ManualTracking\Discrepancy\ManualDiscrepancyIndex;
use App\Http\Livewire\ManualTracking\Distribution\ListManualDistribution;
use App\Http\Livewire\ManualTracking\Shipment\CreateShipment;
use App\Http\Livewire\ManualTracking\Stock\ManualStockIndex;
use App\Http\Livewire\School\SchoolIndex;
use App\Models\ManualTracking\ManualBook;
use App\Models\ManualTracking\ManualDiscrepancy;
use App\Models\Organization;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\Showcase\ShowcaseData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * The search box and "Show N entries" above the manual tracking tables, and the small
 * workflow fixes found while walking the module with each kind of user.
 */
class ManualTrackingTableControlsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $_ENV['SEED_PROFILE'] = 'showcase';
        $this->seed(DatabaseSeeder::class);
        unset($_ENV['SEED_PROFILE']);
    }

    public function test_search_and_page_size_work_on_every_list(): void
    {
        $this->actingAs($this->user('moe.officer'));

        foreach ([ListManualBook::class, ManualStockIndex::class, ManualDiscrepancyIndex::class, ListManualDistribution::class, SchoolIndex::class] as $component) {
            Livewire::test($component)
                ->set('search', 'Oromia')
                ->set('recordes', 25)
                ->assertOk()
                ->assertHasNoErrors();
        }
    }

    public function test_title_search_narrows_the_list(): void
    {
        $this->actingAs($this->user('moe.officer'));
        [$wanted, $other] = ManualBook::query()->orderBy('title')->take(2)->get()->all();

        Livewire::test(ListManualBook::class)
            ->set('search', $wanted->title)
            ->assertSee($wanted->title)
            ->assertDontSee($other->title);
    }

    public function test_field_errors_raise_a_readable_toast(): void
    {
        $this->actingAs(User::query()->where('email', 'superadmin@gmail.com')->firstOrFail());

        Livewire::test(AddManualBook::class)
            ->call('saveBook')
            ->assertHasErrors(['region_id'])
            ->assertDispatchedBrowserEvent('alert', fn ($event, $data) => $data['message'] === 'The region field is required.');
    }

    public function test_a_single_source_is_chosen_for_the_sender(): void
    {
        $officer = User::query()
            ->where('access_level', User::ACCESS_LEVEL_WOREDA)
            ->whereNull('organization_id')
            ->get()
            ->first(fn (User $user) => ! $user->isSchoolUser() && $user->can('manual-tracking.shipments.dispatch'));
        $this->actingAs($officer);

        $office = Organization::query()
            ->where('woreda_id', $officer->woreda_id)
            ->whereDoesntHave('organizationType', fn ($type) => $type->where('name', 'School'))
            ->sole();

        Livewire::test(CreateShipment::class)->assertSet('from_organization_id', (string) $office->id);
    }

    public function test_the_receiver_is_not_offered_a_resolve_form_for_its_own_hand_off(): void
    {
        $discrepancy = ManualDiscrepancy::query()
            ->where('status', ManualDiscrepancy::STATUS_OPEN)
            ->with('line.distribution')
            ->get()
            ->first(fn ($discrepancy) => $discrepancy->line->distribution->received_by);
        $receiver = User::query()->findOrFail($discrepancy->line->distribution->received_by);

        $this->actingAs($receiver);
        if (! $receiver->can('manual-tracking.discrepancies.resolve')) {
            $this->markTestSkipped('The receiver in the showcase data cannot resolve discrepancies at all.');
        }

        Livewire::test(ManualDiscrepancyIndex::class)
            ->assertSee('Awaiting review by someone other than the sender and receiver')
            ->assertDontSeeHtml('wire:click="resolve(' . $discrepancy->id . ')"');
    }

    private function user(string $local): User
    {
        return User::query()->where('email', $local . '@' . ShowcaseData::EMAIL_DOMAIN)->firstOrFail();
    }
}
