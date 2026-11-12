<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Brings a database built by older code up to the shape the later migrations expect.
 *
 * Some early migrations were edited in place after the production database had run them, and
 * two tables were later redefined under new migration names. On a fresh database this does
 * nothing; on such an older database it:
 *
 * - adds the user columns the edited users migration now creates (is_firtst_time,
 *   organization_id, country_id);
 * - drops the old packages and distributions tables when they are empty, so the current
 *   definitions can be created (renaming them would keep foreign key names the new tables
 *   need). A table that still holds rows is left alone and the migration stops, so no data
 *   is lost silently.
 */
return new class extends Migration
{
    private const REDEFINED = [
        // table => a column only the current definition has
        'packages' => 'print_order_id',
        'distributions' => 'name',
    ];

    public function up(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (! Schema::hasColumn('users', 'is_firtst_time')) {
                    $table->string('is_firtst_time')->default('0')->nullable()->after('password');
                }
                if (! Schema::hasColumn('users', 'organization_id')) {
                    $table->foreignId('organization_id')->nullable();
                }
            });

            if (! Schema::hasColumn('users', 'country_id')) {
                Schema::table('users', function (Blueprint $table) {
                    $table->foreignId('country_id')->nullable()->constrained();
                });
            }
        }

        foreach (self::REDEFINED as $table => $currentColumn) {
            if (! Schema::hasTable($table) || Schema::hasColumn($table, $currentColumn)) {
                continue;
            }

            if (DB::table($table)->exists()) {
                throw new RuntimeException("The old {$table} table still holds rows; move them before migrating.");
            }

            Schema::drop($table);
        }
    }

    public function down(): void
    {
        // Moving tables and columns back could drop data the newer code wrote; restore a backup instead.
    }
};
