<?php

namespace Tests\Feature;

use App\Http\Livewire\Distribution\AddDistribution;
use App\Http\Livewire\Distribution\ListDistribution;
use App\Http\Livewire\Route\AddRoute;
use App\Http\Livewire\Route\ListRoute;
use App\Http\Livewire\Trace\TraceBookDistribution;
use App\Http\Livewire\Trace\TraceBookInfo;
use App\Models\Book;
use App\Models\Country;
use App\Models\Distribution;
use App\Models\DistributionRoute;
use App\Models\Grade;
use App\Models\Organization;
use App\Models\Package;
use App\Models\PrintOrder;
use App\Models\Subject;
use App\Models\User;
use App\Models\WareHouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RouteDistributionFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_route_can_be_updated_in_edit_mode(): void
    {
        $fromWarehouse = $this->createWarehouse('Printer Warehouse', 101);
        $toWarehouse = $this->createWarehouse('MoE Warehouse', 202);

        $route = DistributionRoute::create([
            'name' => 'Route Alpha',
            'description' => 'Initial route description',
            'from_ware_house_id' => $fromWarehouse->id,
            'to_ware_house_id' => $toWarehouse->id,
            'is_active' => true,
        ]);

        Livewire::withQueryParams(['edit' => $route->id])
            ->test(AddRoute::class)
            ->set('name', 'Route Alpha Updated')
            ->set('description', 'Updated route description')
            ->set('from_warehouse', $toWarehouse->id)
            ->set('to_warehouse', $fromWarehouse->id)
            ->set('is_active', 0)
            ->call('addRoute');

        $this->assertDatabaseHas('distribution_routes', [
            'id' => $route->id,
            'name' => 'Route Alpha Updated',
            'description' => 'Updated route description',
            'from_ware_house_id' => $toWarehouse->id,
            'to_ware_house_id' => $fromWarehouse->id,
            'is_active' => 0,
        ]);

        $this->assertSame(1, DistributionRoute::count());
    }

    public function test_distribution_can_be_updated_with_reordered_steps(): void
    {
        $warehouseA = $this->createWarehouse('Org A', 301);
        $warehouseB = $this->createWarehouse('Org B', 302);
        $warehouseC = $this->createWarehouse('Org C', 303);

        $routeOne = $this->createRoute('Route One', $warehouseA, $warehouseB);
        $routeTwo = $this->createRoute('Route Two', $warehouseB, $warehouseC);
        $routeThree = $this->createRoute('Route Three', $warehouseC, $warehouseA);

        $distribution = Distribution::create([
            'name' => 'Distribution One',
            'description' => 'Initial distribution',
            'is_active' => true,
        ]);

        $distribution->steps()->createMany([
            ['route_id' => $routeOne->id, 'step_order' => 1],
            ['route_id' => $routeTwo->id, 'step_order' => 2],
        ]);

        Livewire::withQueryParams(['edit' => $distribution->id])
            ->test(AddDistribution::class)
            ->set('name', 'Distribution One Updated')
            ->set('description', 'Updated distribution description')
            ->set('is_active', 0)
            ->set('steps', [
                ['route_id' => $routeTwo->id],
                ['route_id' => $routeThree->id],
            ])
            ->call('save');

        $this->assertDatabaseHas('distributions', [
            'id' => $distribution->id,
            'name' => 'Distribution One Updated',
            'description' => 'Updated distribution description',
            'is_active' => 0,
        ]);

        $orderedSteps = $distribution->fresh()->steps()->orderBy('step_order')->get();
        $this->assertCount(2, $orderedSteps);
        $this->assertSame([$routeTwo->id, $routeThree->id], $orderedSteps->pluck('route_id')->all());
        $this->assertSame([1, 2], $orderedSteps->pluck('step_order')->all());
    }

    public function test_route_list_view_action_redirects_to_route_detail_page(): void
    {
        $fromWarehouse = $this->createWarehouse('Org From', 401);
        $toWarehouse = $this->createWarehouse('Org To', 402);

        $route = $this->createRoute('Viewable Route', $fromWarehouse, $toWarehouse);

        Livewire::test(ListRoute::class)
            ->call('viewRoute', $route->id)
            ->assertRedirect(route('route.view', ['id' => $route->id]));
    }

    public function test_distribution_list_view_action_redirects_to_distribution_detail_page(): void
    {
        $distribution = Distribution::create([
            'name' => 'Viewable Distribution',
            'description' => 'Distribution to view',
            'is_active' => true,
        ]);

        Livewire::test(ListDistribution::class)
            ->call('viewDistribution', $distribution->id)
            ->assertRedirect(route('distribution-details.show', $distribution));
    }

    public function test_route_detail_page_renders_dynamic_route_data(): void
    {
        $user = User::factory()->create();

        $fromWarehouse = $this->createWarehouse('Route Detail From Org', 451);
        $toWarehouse = $this->createWarehouse('Route Detail To Org', 452);
        $route = $this->createRoute('Route Detail Name', $fromWarehouse, $toWarehouse);

        $distribution = Distribution::create([
            'name' => 'Linked Distribution',
            'description' => 'Linked distribution description',
            'is_active' => true,
        ]);

        $distribution->steps()->create([
            'route_id' => $route->id,
            'step_order' => 1,
        ]);

        $this->actingAs($user)
            ->get(route('route.view', ['id' => $route->id]))
            ->assertOk()
            ->assertSee('Route Detail Name')
            ->assertSee('Distribution Usage')
            ->assertSee(route('distribution-details.show', $distribution));
    }

    public function test_distribution_detail_page_renders_dynamic_distribution_data(): void
    {
        $user = User::factory()->create();

        $fromWarehouse = $this->createWarehouse('Detail From Org', 501);
        $toWarehouse = $this->createWarehouse('Detail To Org', 502);
        $route = $this->createRoute('Detail Route', $fromWarehouse, $toWarehouse);

        $distribution = Distribution::create([
            'name' => 'Detail Distribution',
            'description' => 'Detail distribution description',
            'is_active' => true,
        ]);

        $distribution->steps()->create([
            'route_id' => $route->id,
            'step_order' => 1,
        ]);

        $this->actingAs($user)
            ->get(route('distribution-details.show', $distribution))
            ->assertOk()
            ->assertSee('Detail Distribution')
            ->assertSee('Detail Route')
            ->assertSee('Configured');
    }

    public function test_trace_distribution_component_redirects_to_distribution_detail_page(): void
    {
        $distribution = Distribution::create([
            'name' => 'Trace View Distribution',
            'description' => 'Trace view distribution',
            'is_active' => true,
        ]);

        Livewire::test(TraceBookDistribution::class)
            ->call('showDistribution', $distribution->id)
            ->assertRedirect(route('distribution-details.show', $distribution));
    }

    public function test_trace_distribution_component_renders_aggregate_fallback_values(): void
    {
        Distribution::create([
            'name' => 'Aggregate Fallback Distribution',
            'description' => 'Aggregate fallback distribution',
            'is_active' => true,
        ]);

        Livewire::test(TraceBookDistribution::class)
            ->assertSee('Aggregate Fallback Distribution')
            ->assertSee('0 Packages')
            ->assertSee('0 Books');
    }

    public function test_trace_distribution_component_filters_by_grade_and_subject(): void
    {
        $gradeMatch = Grade::create(['name' => 'Grade Filter Match']);
        $gradeOther = Grade::create(['name' => 'Grade Filter Other']);
        $subjectMatch = Subject::create(['name' => 'Subject Filter Match']);
        $subjectOther = Subject::create(['name' => 'Subject Filter Other']);

        $warehouseA = $this->createWarehouse('Filter Org A', 611);
        $warehouseB = $this->createWarehouse('Filter Org B', 612);
        $warehouseC = $this->createWarehouse('Filter Org C', 613);
        $warehouseD = $this->createWarehouse('Filter Org D', 614);

        $matchRoute = $this->createRoute('Filter Route Match', $warehouseA, $warehouseB);
        $otherRoute = $this->createRoute('Filter Route Other', $warehouseC, $warehouseD);

        $matchDistribution = Distribution::create([
            'name' => 'Distribution Filter Match',
            'description' => 'Distribution for filter match',
            'is_active' => true,
        ]);

        $otherDistribution = Distribution::create([
            'name' => 'Distribution Filter Other',
            'description' => 'Distribution for filter mismatch',
            'is_active' => true,
        ]);

        $matchDistribution->steps()->create([
            'route_id' => $matchRoute->id,
            'step_order' => 1,
        ]);

        $otherDistribution->steps()->create([
            'route_id' => $otherRoute->id,
            'step_order' => 1,
        ]);

        $this->createPackageForWarehouseAndFilters($warehouseA, $gradeMatch, $subjectMatch);
        $this->createPackageForWarehouseAndFilters($warehouseC, $gradeOther, $subjectOther);

        Livewire::test(TraceBookDistribution::class, [
            'gradeId' => $gradeMatch->id,
            'subjectId' => $subjectMatch->id,
        ])
            ->assertSee('Distribution Filter Match')
            ->assertDontSee('Distribution Filter Other');
    }

    public function test_trace_book_info_component_filters_summary_by_grade_and_subject(): void
    {
        $gradeMatch = Grade::create(['name' => 'Grade Info Match']);
        $gradeOther = Grade::create(['name' => 'Grade Info Other']);
        $subjectMatch = Subject::create(['name' => 'Subject Info Match']);
        $subjectOther = Subject::create(['name' => 'Subject Info Other']);

        $warehouseMatch = $this->createWarehouse('Info Filter Org A', 701);
        $warehouseOther = $this->createWarehouse('Info Filter Org B', 702);

        $matchPackage = $this->createPackageForWarehouseAndFilters($warehouseMatch, $gradeMatch, $subjectMatch);
        $matchPackage->update([
            'no_of_books' => 999,
        ]);

        $otherPackage = $this->createPackageForWarehouseAndFilters($warehouseOther, $gradeOther, $subjectOther);
        $otherPackage->update([
            'no_of_books' => 300,
            'balance' => 300,
        ]);

        Livewire::test(TraceBookInfo::class, [
            'gradeId' => $gradeMatch->id,
            'subjectId' => $subjectMatch->id,
        ])
            ->assertSee('Grade Info Match')
            ->assertSee('Subject Info Match')
            ->assertSee('120')
            ->assertDontSee('999')
            ->assertDontSee('420');
    }

    private function createWarehouse(string $organizationName, int $branch): WareHouse
    {
        $country = Country::firstOrCreate([
            'name' => 'Testland',
        ], [
            'code' => 'TL',
        ]);

        $organization = Organization::create([
            'name' => $organizationName,
            'country_id' => $country->id,
        ]);

        return WareHouse::create([
            'branch' => $branch,
            'organization_id' => $organization->id,
            'country_id' => $country->id,
        ]);
    }

    private function createRoute(string $name, WareHouse $fromWarehouse, WareHouse $toWarehouse): DistributionRoute
    {
        return DistributionRoute::create([
            'name' => $name,
            'description' => $name . ' description',
            'from_ware_house_id' => $fromWarehouse->id,
            'to_ware_house_id' => $toWarehouse->id,
            'is_active' => true,
        ]);
    }

    private function createPackageForWarehouseAndFilters(WareHouse $warehouse, Grade $grade, Subject $subject): Package
    {
        $book = Book::create([
            'grade_id' => $grade->id,
            'subject_id' => $subject->id,
        ]);

        $printOrder = PrintOrder::create([
            'order_organization_id' => $warehouse->organization_id,
            'printer_organization_id' => $warehouse->organization_id,
            'book_id' => $book->id,
            'no_of_books' => 120,
            'no_of_packages' => 12,
            'print_status' => 1,
            'request_status' => 1,
        ]);

        return Package::create([
            'ware_house_id' => $warehouse->id,
            'print_order_id' => $printOrder->id,
            'sender_organization_id' => $warehouse->organization_id,
            'receiver_organization_id' => $warehouse->organization_id,
            'subject_id' => $subject->id,
            'grade_id' => $grade->id,
            'no_of_books' => 120,
            'books_per_package' => 10,
            'balance' => 120,
        ]);
    }
}
