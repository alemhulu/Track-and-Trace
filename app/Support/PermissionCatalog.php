<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Arr;

/**
 * Reads the permission catalog in config/permissions.php, which mirrors the sidebar.
 */
class PermissionCatalog
{
    /**
     * Sections in sidebar order, each holding its modules; a module may split into sub-modules.
     *
     * @return array<string, array<string, array{route?: string, label: string, icon: string, permissions: array<string, string>, submodules?: array<string, array{label: string, permissions: array<string, string>}>}>>
     */
    public static function sections(): array
    {
        return config('permissions.sections', []);
    }

    /**
     * Every permission name in the catalog.
     *
     * @return array<int, string>
     */
    public static function names(): array
    {
        return array_keys(self::labels());
    }

    /**
     * Permission names of a module including its sub-modules ("locations"), or of one
     * sub-module ("locations.regions").
     *
     * @return array<int, string>
     */
    public static function namesOf(string $key): array
    {
        foreach (self::sections() as $modules) {
            if (isset($modules[$key])) {
                return array_keys(self::permissionsOf($modules[$key]));
            }

            foreach ($modules as $module) {
                if (isset($module['submodules'][$key])) {
                    return array_keys($module['submodules'][$key]['permissions']);
                }
            }
        }

        return [];
    }

    /**
     * The "view" permission of every module in a section, used to show its sidebar heading.
     *
     * @return array<int, string>
     */
    public static function viewPermissionsOf(string $section): array
    {
        return collect(self::sections()[$section] ?? [])
            ->map(fn ($module) => array_key_first($module['permissions']))
            ->values()
            ->all();
    }

    /**
     * Route name of the first sidebar page the user may open, used as their landing page.
     */
    public static function homeRouteFor(User $user): ?string
    {
        foreach (self::sections() as $modules) {
            foreach ($modules as $module) {
                if (! empty($module['route']) && $user->can(array_key_first($module['permissions']))) {
                    return $module['route'];
                }
            }
        }

        return null;
    }

    /**
     * Labels of the modules the given permissions touch, in sidebar order.
     *
     * @param  iterable<int, string>  $names
     * @return array<int, string>
     */
    public static function moduleLabelsFor(iterable $names): array
    {
        $names = collect($names);

        return collect(self::sections())->flatten(1)
            ->filter(fn ($module) => $names->intersect(array_keys(self::permissionsOf($module)))->isNotEmpty())
            ->pluck('label')
            ->values()
            ->all();
    }

    /**
     * Human label of a permission, falling back to its name.
     */
    public static function labelOf(string $name): string
    {
        return self::labels()[$name] ?? $name;
    }

    /**
     * A module's own permissions followed by its sub-modules'.
     *
     * @return array<string, string>
     */
    public static function permissionsOf(array $module): array
    {
        return array_merge($module['permissions'], ...array_values(array_column($module['submodules'] ?? [], 'permissions')));
    }

    /**
     * @return array<string, string>
     */
    private static function labels(): array
    {
        return Arr::collapse(
            collect(self::sections())->flatten(1)->map(fn ($module) => self::permissionsOf($module))->all()
        );
    }
}
