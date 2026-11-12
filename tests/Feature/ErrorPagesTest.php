<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ErrorPagesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware('web')->get('/_test/abort/{code}/{reason?}', fn (int $code, ?string $reason = null) => abort($code, $reason ?? ''));
        Route::middleware('web')->get('/_test/crash', fn () => throw new \RuntimeException('secret database detail'));
    }

    public function test_an_unknown_address_shows_the_branded_not_found_page(): void
    {
        $this->get('/no-such-page')
            ->assertNotFound()
            ->assertSee("We couldn't find that page")
            ->assertSee('Track <span>&amp;</span> Trace', false)
            ->assertSee('/no-such-page');
    }

    public function test_each_status_has_its_own_page(): void
    {
        $titles = [
            401 => 'Sign in to continue',
            403 => "You don't have access to this page",
            405 => "That action isn't available here",
            419 => 'Your session expired',
            429 => 'Too many requests',
            500 => 'Something went wrong on our side',
            503 => "We'll be back shortly",
            410 => "This request couldn't be completed",
            502 => 'The service is having trouble',
        ];

        foreach ($titles as $code => $title) {
            $this->get("/_test/abort/{$code}")->assertStatus($code)->assertSee($title);
        }
    }

    public function test_a_reason_given_with_abort_is_shown_on_client_errors(): void
    {
        $this->get('/_test/abort/403/Only regional users can do this')
            ->assertForbidden()
            ->assertSee('Only regional users can do this');
    }

    public function test_a_crash_never_shows_its_details(): void
    {
        config(['app.debug' => false]);

        $this->get('/_test/crash')
            ->assertStatus(500)
            ->assertSee('Something went wrong on our side')
            ->assertDontSee('secret database detail');
    }
}
