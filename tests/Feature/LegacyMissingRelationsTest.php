<?php

namespace Tests\Feature;

use App\Http\Livewire\Book\ListBook;
use App\Http\Livewire\Print\ListPrintOrder;
use App\Http\Livewire\PrintRequest;
use App\Models\Book;
use App\Models\Country;
use App\Models\Organization;
use App\Models\Package;
use App\Models\PrintOrder;
use App\Models\User;
use App\Models\WareHouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Books imported long ago may have no grade or subject. The book and print-order screens show a
 * dash for them instead of failing, and approving such an order says what is missing.
 */
class LegacyMissingRelationsTest extends TestCase
{
    use RefreshDatabase;

    private PrintOrder $order;

    protected function setUp(): void
    {
        parent::setUp();
        $this->grantModuleAccessToNewUsers();
        $this->actingAs(User::factory()->create(['access_level' => User::ACCESS_LEVEL_NATIONAL]));

        $country = Country::query()->create(['name' => 'Ethiopia', 'code' => 'ET']);
        $printer = Organization::query()->create(['name' => 'National Printer', 'country_id' => $country->id]);
        WareHouse::query()->create(['branch' => 1, 'organization_id' => $printer->id, 'country_id' => $country->id]);

        $book = Book::query()->create(['grade_id' => null, 'subject_id' => null]);
        $this->order = PrintOrder::query()->create([
            'order_organization_id' => $printer->id,
            'printer_organization_id' => $printer->id,
            'book_id' => $book->id,
            'no_of_books' => 120,
            'no_of_packages' => 12,
            'print_status' => 1,
            'request_status' => 1,
        ]);
    }

    public function test_book_and_print_order_lists_show_a_book_without_grade_or_subject(): void
    {
        Livewire::test(ListBook::class)->assertOk()->assertSee('Grade —', false);
        Livewire::test(ListPrintOrder::class)->assertOk()->assertSee('Grade —', false);
        Livewire::test(PrintRequest::class, ['id' => $this->order->id])->assertOk();
    }

    public function test_approving_an_order_for_such_a_book_is_refused_and_changes_nothing(): void
    {
        Livewire::test(PrintRequest::class, ['id' => $this->order->id])
            ->call('status', 2)
            ->assertDispatchedBrowserEvent('alert', fn ($event, $data) => str_contains($data['message'], 'no grade or subject'));

        $this->assertSame(1, (int) $this->order->fresh()->request_status);
        $this->assertSame(0, Package::query()->count());
    }

    public function test_the_unbuilt_kebele_page_is_not_routed(): void
    {
        $this->get('/location/kebele')->assertNotFound();
    }
}
