<?php

use App\Support\AcademicYear;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\PermissionRegistrar;

/**
 * Academic years the Ministry runs (Ethiopian calendar, e.g. 2019 E.C.). The one marked
 * current is the default year on every screen that filters by year.
 */
return new class extends Migration
{
    private const PERMISSIONS = [
        'academic-years.view',
        'academic-years.create',
        'academic-years.edit',
        'academic-years.delete',
    ];

    public function up(): void
    {
        Schema::create('academic_years', function (Blueprint $table): void {
            $table->id();
            $table->unsignedSmallInteger('year')->unique();
            $table->date('starts_on');
            $table->date('ends_on');
            $table->boolean('is_current')->default(false)->index();
            $table->string('notes')->nullable();
            $table->timestamps();
        });

        // Start with the year the calendar says we are in, marked current so nothing changes until
        // an admin does, plus every year existing records already use so year pickers keep them
        $current = AcademicYear::calendarYear();
        $years = collect([$current]);
        foreach (\App\Models\AcademicYear::USED_BY as $table => $label) {
            if (Schema::hasTable($table)) {
                $years = $years->merge(DB::table($table)->distinct()->pluck('academic_year'));
            }
        }

        $years->map(fn ($year) => (int) $year)->filter(fn ($year) => $year >= 1990 && $year <= 2100)->unique()->sort()
            ->each(function (int $year) use ($current): void {
                [$startsOn, $endsOn] = AcademicYear::defaultDates($year);
                DB::table('academic_years')->insert([
                    'year' => $year,
                    'starts_on' => $startsOn,
                    'ends_on' => $endsOn,
                    'is_current' => $year === $current,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });

        if (! Schema::hasTable('permissions')) {
            return;
        }

        foreach (self::PERMISSIONS as $name) {
            DB::table('permissions')->insertOrIgnore(['name' => $name, 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()]);
        }
        $permissionIds = DB::table('permissions')->whereIn('name', self::PERMISSIONS)->pluck('id', 'name');

        // Admins manage years; every other role that can see the admin area gets read-only access
        $admins = DB::table('roles')->whereIn('name', ['Super-Admin', 'Admin', 'National Admin'])->pluck('id');
        $viewers = DB::table('roles')->whereIn('name', ['Region Officer', 'Zone Officer', 'Woreda Officer'])->pluck('id');
        $grants = [];
        foreach ($admins as $roleId) {
            foreach ($permissionIds as $permissionId) {
                $grants[] = ['permission_id' => $permissionId, 'role_id' => $roleId];
            }
        }
        foreach ($viewers as $roleId) {
            $grants[] = ['permission_id' => $permissionIds['academic-years.view'], 'role_id' => $roleId];
        }
        DB::table('role_has_permissions')->insertOrIgnore($grants);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_years');

        if (Schema::hasTable('permissions')) {
            DB::table('permissions')->whereIn('name', self::PERMISSIONS)->delete();
            app(PermissionRegistrar::class)->forgetCachedPermissions();
        }
    }
};
