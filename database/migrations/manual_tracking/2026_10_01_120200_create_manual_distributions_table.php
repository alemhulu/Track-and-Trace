<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('manual_distributions')) {
            return;
        }

        Schema::create('manual_distributions', function (Blueprint $table): void {
            $table->id();
            $table->string('reference')->unique();
            $table->unsignedBigInteger('distributed_by')->nullable();
            $table->unsignedBigInteger('organization_id')->nullable();
            $table->unsignedBigInteger('country_id')->nullable();
            $table->unsignedBigInteger('region_id')->nullable();
            $table->unsignedBigInteger('zone_id')->nullable();
            $table->unsignedBigInteger('woreda_id')->nullable();
            $table->unsignedBigInteger('destination_organization_id')->nullable();
            $table->unsignedBigInteger('destination_country_id')->nullable();
            $table->unsignedBigInteger('destination_region_id')->nullable();
            $table->unsignedBigInteger('destination_zone_id')->nullable();
            $table->unsignedBigInteger('destination_woreda_id')->nullable();
            $table->timestamp('distributed_at')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index(['organization_id', 'region_id', 'zone_id', 'woreda_id'], 'manual_dist_src_scope_idx');
            $table->index(['destination_organization_id', 'destination_region_id', 'destination_zone_id', 'destination_woreda_id'], 'manual_dist_dest_scope_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manual_distributions');
    }
};
