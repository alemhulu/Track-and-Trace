<?php

namespace Tests\Feature;

use App\Http\Livewire\AcademicYear\AcademicYearIndex;
use App\Http\Livewire\ManualTracking\Allocation\AllocationIndex;
use App\Models\AcademicYear;
use App\Models\ManualTracking\ManualAllocation;
use App\Models\User;
use App\Support\AcademicYear as AcademicYearCalendar;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class AcademicYearManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->grantModuleAccessToNewUsers();
        AcademicYear::query()->delete();
        AcademicYearCalendar::flush();
        $this->admin = User::factory()->create();
    }

    public function test_the_calendar_rolls_over_on_meskerem_1(): void
    {
        $this->assertSame(2018, AcademicYearCalendar::calendarYear(Carbon::parse('2026-09-10')));
        $this->assertSame(2019, AcademicYearCalendar::calendarYear(Carbon::parse('2026-09-11')));
        // Meskerem 1 falls on 12 September before a Gregorian leap year
        $this->assertSame(2015, AcademicYearCalendar::calendarYear(Carbon::parse('2023-09-11')));
        $this->assertSame(2016, AcademicYearCalendar::calendarYear(Carbon::parse('2023-09-12')));
        $this->assertSame(['2023-09-12', '2024-09-10'], AcademicYearCalendar::defaultDates(2016));
    }

    public function test_an_admin_adds_a_year_and_makes_it_current(): void
    {
        Livewire::actingAs($this->admin)->test(AcademicYearIndex::class)
            ->set('year', 2018)
            ->assertSet('starts_on', '2025-09-11')
            ->call('save')
            ->assertHasNoErrors()
            ->set('year', 2019)
            ->set('make_current', true)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame([2018, 2019], AcademicYear::query()->orderBy('year')->pluck('year')->all());
        $this->assertSame(2019, AcademicYearCalendar::current());
        $this->assertSame([2019, 2018], array_keys(AcademicYearCalendar::options()));

        $earlier = AcademicYear::query()->where('year', 2018)->first();
        Livewire::actingAs($this->admin)->test(AcademicYearIndex::class)->call('makeCurrent', $earlier->id);

        $this->assertSame(2018, AcademicYearCalendar::current());
        $this->assertSame(1, AcademicYear::query()->where('is_current', true)->count());
    }

    public function test_years_cannot_repeat_or_overlap(): void
    {
        AcademicYear::query()->create(['year' => 2019, 'starts_on' => '2026-09-11', 'ends_on' => '2027-09-10', 'is_current' => true]);

        Livewire::actingAs($this->admin)->test(AcademicYearIndex::class)
            ->set('year', 2019)->call('save')->assertHasErrors(['year' => 'unique'])
            ->set('year', 2020)->set('starts_on', '2027-09-01')->set('ends_on', '2028-09-10')->call('save')->assertHasErrors('starts_on')
            ->set('starts_on', '2027-09-11')->set('ends_on', '2027-09-01')->call('save')->assertHasErrors(['ends_on' => 'after']);
    }

    public function test_the_current_year_and_years_with_records_are_kept(): void
    {
        $current = AcademicYear::query()->create(['year' => 2019, 'starts_on' => '2026-09-11', 'ends_on' => '2027-09-10', 'is_current' => true]);
        $used = AcademicYear::query()->create(['year' => 2018, 'starts_on' => '2025-09-11', 'ends_on' => '2026-09-10']);
        $unused = AcademicYear::query()->create(['year' => 2017, 'starts_on' => '2024-09-11', 'ends_on' => '2025-09-10']);
        DB::table('manual_allocation_imports')->insert(['academic_year' => '2018', 'created_at' => now(), 'updated_at' => now()] + $this->importColumns());

        Livewire::actingAs($this->admin)->test(AcademicYearIndex::class)
            ->call('delete', $current->id)
            ->call('delete', $used->id)
            ->call('delete', $unused->id);

        $this->assertSame([2018, 2019], AcademicYear::query()->orderBy('year')->pluck('year')->all());
    }

    public function test_viewers_cannot_change_years(): void
    {
        $year = AcademicYear::query()->create(['year' => 2019, 'starts_on' => '2026-09-11', 'ends_on' => '2027-09-10']);
        $viewer = User::factory()->create();
        $viewer->syncPermissions(['academic-years.view']);

        $this->actingAs($viewer)->get(route('academic-years.index'))->assertOk()->assertSee('2019 E.C.')->assertDontSee('Add year');
        Livewire::actingAs($viewer)->test(AcademicYearIndex::class)->call('makeCurrent', $year->id)->assertForbidden();
        Livewire::actingAs($viewer)->test(AcademicYearIndex::class)->set('year', 2020)->call('save')->assertForbidden();

        $outsider = User::factory()->create();
        $outsider->syncPermissions([]);
        $this->actingAs($outsider)->get(route('academic-years.index'))->assertForbidden();
    }

    public function test_manual_tracking_uses_the_managed_years(): void
    {
        AcademicYear::ensure(2018);
        AcademicYear::ensure(2019, current: true);

        $this->assertSame('2019', ManualAllocation::currentYear());

        // The allocation screen opens on the current year, offers only managed years and
        // refuses imports for a year that is not set up
        Livewire::actingAs($this->admin)->test(AllocationIndex::class)
            ->assertSet('year', '2019')
            ->assertViewHas('years', fn ($years) => array_keys($years) === [2019, 2018])
            ->set('year', '2031')
            ->assertSet('year', '2019')
            ->set('importYear', '2031')
            ->call('checkWorkbook')
            ->assertHasErrors(['importYear' => 'in']);

        // A year in the address that is not set up falls back to the current one
        $this->assertSame(2018, AcademicYearCalendar::resolve('2018'));
        $this->assertSame(2019, AcademicYearCalendar::resolve('2031'));
        $this->assertSame(2019, AcademicYearCalendar::resolve(null));

        $this->artisan('manual:import-allocations', ['file' => __FILE__, '--year' => 2031])->assertFailed();
    }

    // Required columns of manual_allocation_imports other than the year
    private function importColumns(): array
    {
        $columns = collect(DB::getSchemaBuilder()->getColumns('manual_allocation_imports'))
            ->reject(fn ($column) => $column['nullable'] || $column['default'] !== null || $column['auto_increment']
                || in_array($column['name'], ['academic_year', 'created_at', 'updated_at'], true));

        return $columns->mapWithKeys(fn ($column) => [$column['name'] => str_contains($column['type'], 'int') ? 0 : 'x'])->all();
    }
}
