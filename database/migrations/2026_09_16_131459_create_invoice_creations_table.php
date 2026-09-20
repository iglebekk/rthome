<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_creations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('club_id')->constrained()->cascadeOnDelete();
            $table->uuid('submission_token')->unique();
            $table->string('status', 20)->default('draft');
            $table->date('invoice_date')->nullable();
            $table->date('due_date')->nullable();
            $table->timestamp('issued_at')->nullable();
            $table->timestamps();

            $table->index(['club_id', 'status', 'created_at']);
        });

        Schema::create('invoice_creation_member', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_creation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['invoice_creation_id', 'member_id']);
        });

        Schema::create('invoice_creation_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_creation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('quantity', 12, 2);
            $table->unsignedInteger('position');
            $table->timestamps();

            $table->unique(['invoice_creation_id', 'product_id']);
            $table->unique(['invoice_creation_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_creation_lines');
        Schema::dropIfExists('invoice_creation_member');
        Schema::dropIfExists('invoice_creations');
    }
};
