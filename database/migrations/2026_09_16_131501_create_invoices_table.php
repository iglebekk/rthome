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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('club_id')->constrained()->restrictOnDelete();
            $table->foreignId('member_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('credited_invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
            $table->string('document_type', 20)->default('invoice');
            $table->string('status', 20)->default('issued');
            $table->unsignedBigInteger('number')->nullable();
            $table->date('invoice_date');
            $table->date('due_date');
            $table->timestamp('issued_at');
            $table->string('club_name');
            $table->string('club_organization_number', 9);
            $table->string('recipient_name');
            $table->string('recipient_company_name')->nullable();
            $table->string('recipient_organization_number', 9)->nullable();
            $table->string('recipient_address')->nullable();
            $table->string('recipient_postal_code', 20)->nullable();
            $table->string('recipient_city')->nullable();
            $table->string('recipient_email')->nullable();
            $table->bigInteger('net_total_ore');
            $table->bigInteger('vat_total_ore');
            $table->bigInteger('gross_total_ore');
            $table->timestamps();

            $table->unique(['club_id', 'number']);
            $table->index(['club_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
