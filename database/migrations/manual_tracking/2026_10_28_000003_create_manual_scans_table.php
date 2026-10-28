<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Every QR scan made while sorting or receiving packages. A package QR only carries the
 * zone name, so a scan records the zone it resolved to, the title the operator was
 * counting, and whether the package was somewhere it should not be (a misroute alert).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('manual_scans', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignId('manual_distribution_id')->nullable()->constrained('manual_distributions')->nullOnDelete();
            $table->string('context', 20);
            $table->string('payload');
            $table->string('kind', 20);
            $table->foreignId('zone_id')->nullable()->constrained('zones')->nullOnDelete();
            $table->foreignId('manual_book_id')->nullable()->constrained('manual_books')->nullOnDelete();
            $table->string('edition', 20)->nullable();
            $table->string('flag', 30)->nullable();
            $table->string('flag_message')->nullable();
            $table->string('alert_status', 20)->nullable();
            $table->unsignedBigInteger('scanned_by')->nullable();
            $table->timestamp('scanned_at');
            $table->timestamp('voided_at')->nullable();
            $table->unsignedBigInteger('resolved_by')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->string('resolution_note', 1000)->nullable();
            $table->uuid('client_uuid')->nullable()->unique();
            $table->timestamps();

            $table->index(['organization_id', 'context', 'scanned_at'], 'manual_scan_place_idx');
            $table->index(['alert_status'], 'manual_scan_alert_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manual_scans');
    }
};
