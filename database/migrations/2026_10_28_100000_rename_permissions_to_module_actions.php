<?php

use App\Support\PermissionCatalog;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /**
     * Old permission names (and their aliases) → the catalog permissions that replace them.
     * Roles and users holding an old name are given every permission it maps to, so nobody
     * loses access they had: "view" of a module now covers viewing each of its screens, and
     * "manage" covers creating, editing and deleting on each of them.
     *
     * @return array<string, array<int, string>>
     */
    private function map(): array
    {
        $views = fn (string $module) => array_values(array_filter(PermissionCatalog::namesOf($module), fn ($name) => str_ends_with($name, '.view')));
        $writes = fn (string $module) => array_values(array_filter(PermissionCatalog::namesOf($module), fn ($name) => preg_match('/\.(create|edit|delete)$/', $name)));
        $bookSettings = ['books.subjects', 'books.grades', 'books.types', 'books.print-types', 'books.paper-sizes'];

        return [
            'view-dashboard' => ['dashboard.view', 'dashboard.inventory.view', 'dashboard.distribution.view'],

            'view-users' => ['users.view'],
            'view-user' => ['users.view'],
            'user-list' => ['users.view'],
            'create-users' => ['users.create'],
            'user-create' => ['users.create'],
            'edit-users' => ['users.edit'],
            'user-edit' => ['users.edit'],
            'delete-users' => ['users.delete'],
            'user-delete' => ['users.delete'],

            'view-roles' => ['roles.view'],
            'view-role' => ['roles.view'],
            'role-list' => ['roles.view'],
            'create-roles' => ['roles.create'],
            'role-create' => ['roles.create'],
            'edit-roles' => ['roles.edit'],
            'role-edit' => ['roles.edit'],
            'delete-roles' => ['roles.delete'],
            'role-delete' => ['roles.delete'],

            'view-locations' => $views('locations'),
            'manage-locations' => $writes('locations'),
            'view-organizations' => ['organizations.view', 'organizations.types.view'],
            'manage-organizations' => ['organizations.create', 'organizations.delete', ...$writes('organizations.types'), 'warehouses.create'],

            'view-manual-tracking' => array_values(array_diff(
                PermissionCatalog::namesOf('manual-tracking'),
                PermissionCatalog::namesOf('manual-tracking.titles')
            )),

            'view-books' => ['books.view', ...array_merge(...array_map($views, $bookSettings))],
            'create-books' => ['books.create'],
            'edit-books' => ['books.edit', ...array_merge(...array_map($writes, $bookSettings))],
            'delete-books' => ['books.delete'],

            'view-print-orders' => ['print-orders.view'],
            'create-print-orders' => ['print-orders.create'],
            'manage-print-orders' => ['print-orders.edit'],

            'view-packages' => ['packages.view'],
            'create-packages' => ['packages.request', 'packages.send'],
            'update-package-status' => ['packages.send'],

            'view-warehouses' => ['warehouses.view'],
            'manage-warehouses' => ['warehouses.create', 'warehouses.edit', 'warehouses.delete'],

            'view-routes' => ['routes.view'],
            'manage-routes' => ['routes.create', 'routes.edit', 'routes.delete'],

            'view-distributions' => ['distributions.view'],
            'create-distributions' => ['distributions.create'],
            'update-distributions' => ['distributions.edit', 'distributions.delete'],

            'view-trace' => ['trace.view'],
            'view-logs' => ['logs.view'],
        ];
    }

    public function up(): void
    {
        foreach (PermissionCatalog::names() as $name) {
            $this->permissionId($name);
        }

        foreach ($this->map() as $old => $targets) {
            $oldId = DB::table('permissions')->where('name', $old)->where('guard_name', 'web')->value('id');
            if (! $oldId) {
                continue;
            }

            foreach ($targets as $target) {
                $this->copyGrants($oldId, $this->permissionId($target));
            }

            $this->deletePermission($oldId);
        }

        // Registering titles used to be decided by the Admin role itself
        $adminId = DB::table('roles')->where('name', 'Admin')->where('guard_name', 'web')->value('id');
        if ($adminId) {
            DB::table('role_has_permissions')->insertOrIgnore(array_map(
                fn ($name) => ['permission_id' => $this->permissionId($name), 'role_id' => $adminId],
                PermissionCatalog::namesOf('manual-tracking.titles')
            ));
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        foreach ($this->map() as $old => $targets) {
            $targetId = DB::table('permissions')->where('name', $targets[0])->where('guard_name', 'web')->value('id');
            if ($targetId) {
                $this->copyGrants($targetId, $this->permissionId($old));
            }
        }

        $catalogIds = DB::table('permissions')->whereIn('name', PermissionCatalog::names())->pluck('id');
        foreach ($catalogIds as $id) {
            $this->deletePermission($id);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function permissionId(string $name): int
    {
        $id = DB::table('permissions')->where('name', $name)->where('guard_name', 'web')->value('id');

        return $id ?: DB::table('permissions')->insertGetId([
            'name' => $name,
            'guard_name' => 'web',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function copyGrants(int $fromId, int $toId): void
    {
        $roleRows = DB::table('role_has_permissions')->where('permission_id', $fromId)->get()
            ->map(fn ($row) => ['permission_id' => $toId, 'role_id' => $row->role_id])
            ->all();
        DB::table('role_has_permissions')->insertOrIgnore($roleRows);

        $modelRows = DB::table('model_has_permissions')->where('permission_id', $fromId)->get()
            ->map(fn ($row) => ['permission_id' => $toId, 'model_type' => $row->model_type, 'model_id' => $row->model_id])
            ->all();
        DB::table('model_has_permissions')->insertOrIgnore($modelRows);
    }

    private function deletePermission(int $id): void
    {
        DB::table('role_has_permissions')->where('permission_id', $id)->delete();
        DB::table('model_has_permissions')->where('permission_id', $id)->delete();
        DB::table('permissions')->where('id', $id)->delete();
    }
};
