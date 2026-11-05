<?php

namespace App\Services\ManualTracking;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Leaves only what the imported allocation workbooks produced: their titles, allocations
 * and import records, the regions, zones and woredas they name, and the grades and subjects
 * their titles use. Seeded demo data goes: manual-tracking activity, the print module's
 * books, orders and packages, organizations, warehouses, and every user except the system
 * accounts. Lookup lists (countries, book/print types, paper sizes, organization types,
 * ownerships, sectors, roles and permissions) are kept.
 */
class ImportedDataReset
{
    /** Accounts that survive the reset; the sectors are owned by the super admin */
    public const KEPT_USERS = ['admin@gmail.com', 'superadmin@gmail.com'];

    /** Emptied completely */
    private const ACTIVITY_TABLES = [
        // Manual tracking
        'manual_book_packages', 'manual_book_codes', 'manual_distribution_lines', 'manual_distributions',
        'manual_stock_balances', 'manual_stock_ledgers', 'manual_discrepancies', 'manual_scans', 'manual_audits',
        'field_sync_actions', 'verification_visit_items', 'verification_visits',
        'student_book_issues', 'teacher_guide_issues', 'school_enrolments', 'students',
        // Print module
        'deliveries', 'distribution_steps', 'distribution_routes', 'distributions', 'packages', 'print_orders', 'books',
        'grade_subjects',
        // Places and people no file lists
        'ware_houses', 'organizations', 'sessions', 'personal_access_tokens', 'password_resets',
    ];

    /**
     * @return array<string, int> rows removed per table
     */
    public function run(): array
    {
        $removed = [];
        $count = fn (string $table) => Schema::hasTable($table) ? DB::table($table)->count() : 0;

        Schema::disableForeignKeyConstraints();

        try {
            foreach (self::ACTIVITY_TABLES as $table) {
                if ($removed[$table] = $count($table)) {
                    DB::table($table)->truncate();
                }
            }

            // Titles the workbooks did not bring
            $removed['manual_books'] = DB::table('manual_books')->whereNull('source_key')->delete();

            // Users: only the system accounts, detached from any place
            $kept = DB::table('users')->whereIn('email', self::KEPT_USERS)->pluck('id');
            $gone = DB::table('users')->whereNotIn('id', $kept)->pluck('id');
            DB::table('model_has_roles')->where('model_type', User::class)->whereIn('model_id', $gone)->delete();
            DB::table('model_has_permissions')->where('model_type', User::class)->whereIn('model_id', $gone)->delete();
            $removed['users'] = DB::table('users')->whereIn('id', $gone)->delete();
            DB::table('users')->whereIn('id', $kept)->update([
                'organization_id' => null, 'region_id' => null, 'zone_id' => null, 'woreda_id' => null,
            ]);

            // Places: what the allocations name
            $woredas = DB::table('manual_woreda_allocations')->distinct()->pluck('woreda_id');
            $removed['woredas'] = DB::table('woredas')->whereNotIn('id', $woredas)->delete();

            $zones = DB::table('manual_allocations')->whereNotNull('zone_id')->distinct()->pluck('zone_id')
                ->merge(DB::table('manual_woreda_allocations')->distinct()->pluck('zone_id'))
                ->merge(DB::table('woredas')->whereNotNull('zone_id')->distinct()->pluck('zone_id'))
                ->unique()->values();
            $removed['zones'] = DB::table('zones')->whereNotIn('id', $zones)->delete();

            $regions = DB::table('manual_allocations')->distinct()->pluck('region_id')
                ->merge(DB::table('zones')->whereNotNull('region_id')->distinct()->pluck('region_id'))
                ->unique()->values();
            $removed['regions'] = DB::table('regions')->whereNotIn('id', $regions)->delete();

            // Grades and subjects: what the imported titles use
            $removed['grades'] = DB::table('grades')
                ->whereNotIn('id', DB::table('manual_books')->whereNotNull('grade_id')->distinct()->pluck('grade_id'))->delete();
            $removed['subjects'] = DB::table('subjects')
                ->whereNotIn('id', DB::table('manual_books')->whereNotNull('subject_id')->distinct()->pluck('subject_id'))->delete();
        } finally {
            Schema::enableForeignKeyConstraints();
        }

        return array_filter($removed);
    }
}
