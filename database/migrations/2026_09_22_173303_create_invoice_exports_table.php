<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('invoice_exports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('club_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->json('invoice_ids');
            $table->uuid('batch_id')->nullable();
            $table->string('status', 20)->default('pending');
            $table->string('archive_path')->nullable();
            $table->timestamp('expires_at');
            $table->timestamps();

            $table->index(['status', 'expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_exports');
    }
};
