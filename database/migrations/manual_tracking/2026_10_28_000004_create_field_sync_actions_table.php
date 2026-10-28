<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Work recorded offline in the field app and synced later. Each action carries the id the
 * phone gave it, so sending the same queue twice applies it once.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('field_sync_actions', function (Blueprint $table): void {
            $table->id();
            $table->uuid('client_uuid')->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('organization_id')->nullable()->constrained('organizations')->nullOnDelete();
            $table->string('type', 30);
            $table->json('payload');
            $table->timestamp('recorded_at');
            $table->string('status', 20);
            $table->string('message', 500)->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at'], 'field_sync_user_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('field_sync_actions');
    }
};
