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
 *   organization_id, country_id), dropping foreign keys that point at tables which no
 *   longer exist, since MySQL refuses to rebuild users while they are there;
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
                if (! Schema::hasColumn('users', 'country_id')) {
                    $table->foreignId('country_id')->nullable();
                }
            });

            // A foreign key to a table that no longer exists (users.school_id -> schools) makes
            // MySQL reject every rebuild of users; it guards nothing, so it goes
            $this->dropDanglingForeignKeys('users');

            if (! $this->hasForeignKey('users', 'users_country_id_foreign')) {
                Schema::table('users', function (Blueprint $table) {
                    $table->foreign('country_id')->references('id')->on('countries');
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

    private function dropDanglingForeignKeys(string $table): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        $dangling = DB::select(
            'SELECT k.CONSTRAINT_NAME AS name FROM information_schema.KEY_COLUMN_USAGE k
             LEFT JOIN information_schema.TABLES t ON t.TABLE_SCHEMA = k.REFERENCED_TABLE_SCHEMA AND t.TABLE_NAME = k.REFERENCED_TABLE_NAME
             WHERE k.TABLE_SCHEMA = DATABASE() AND k.TABLE_NAME = ? AND k.REFERENCED_TABLE_NAME IS NOT NULL AND t.TABLE_NAME IS NULL',
            [$table]
        );

        foreach (collect($dangling)->pluck('name')->unique() as $name) {
            DB::statement("ALTER TABLE `{$table}` DROP FOREIGN KEY `{$name}`");
        }
    }

    private function hasForeignKey(string $table, string $name): bool
    {
        if (DB::getDriverName() !== 'mysql') {
            return true;
        }

        return DB::table('information_schema.TABLE_CONSTRAINTS')
            ->where('CONSTRAINT_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', $table)
            ->where('CONSTRAINT_NAME', $name)
            ->where('CONSTRAINT_TYPE', 'FOREIGN KEY')
            ->exists();
    }

    public function down(): void
    {
        // Moving tables and columns back could drop data the newer code wrote; restore a backup instead.
    }
};
