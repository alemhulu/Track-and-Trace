<?php

namespace Tests\Feature;

use App\Http\Livewire\Oganization\AddOrganization;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class UiStabilityRegressionTest extends TestCase
{
    use RefreshDatabase;

    public function test_permission_aliases_used_by_navigation_are_created(): void
    {
        $this->seed(PermissionSeeder::class);

        $this->assertTrue(Permission::where('name', 'view-user')->exists());
        $this->assertTrue(Permission::where('name', 'view-role')->exists());
        $this->assertTrue(Permission::where('name', 'user-list')->exists());
        $this->assertTrue(Permission::where('name', 'role-list')->exists());
    }

    public function test_organization_user_selection_uses_real_user_name(): void
    {
        $user = User::factory()->create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
        ]);

        $component = Livewire::test(AddOrganization::class)
            ->set('user_id', $user->id)
            ->call('selectUser');

        $this->assertSame('Jane Doe', $component->get('user'));
    }
}
