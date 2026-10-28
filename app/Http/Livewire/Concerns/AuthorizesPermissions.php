<?php

namespace App\Http\Livewire\Concerns;

use Closure;
use Illuminate\Support\Facades\Auth;

/**
 * Checks catalog permissions (config/permissions.php) on every Livewire request, not just
 * the first page load: the component's view permission when it boots, and the action's
 * permission before any wire:click, form submit or event listener method runs.
 *
 * The component lists them in permissionMap():
 *   'view'    => permission needed to load the component, or "a|b" for any of several
 *   'actions' => [method => permission, or a Closure returning one, e.g. create vs edit
 *                 depending on whether a record is being edited]
 * Methods left out of 'actions' need nothing beyond the view permission.
 */
trait AuthorizesPermissions
{
    /**
     * @return array{view?: string, actions?: array<string, string|Closure>}
     */
    abstract protected function permissionMap(): array;

    public function bootAuthorizesPermissions(): void
    {
        $this->authorizePermission($this->permissionMap()['view'] ?? null);
    }

    public function callMethod($method, $params = [], $captureReturnValueCallback = null)
    {
        $permission = $this->permissionMap()['actions'][trim($method)] ?? null;
        $this->authorizePermission($permission instanceof Closure ? $permission() : $permission);

        return parent::callMethod($method, $params, $captureReturnValueCallback);
    }

    protected function authorizePermission(?string $permission): void
    {
        if ($permission !== null) {
            abort_unless(Auth::user()?->canAny(explode('|', $permission)), 403);
        }
    }
}
