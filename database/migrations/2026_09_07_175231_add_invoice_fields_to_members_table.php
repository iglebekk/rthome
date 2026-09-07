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
        Schema::table('members', function (Blueprint $table) {
            $table->string('invoice_company_name')->nullable();
            $table->string('invoice_organization_number', 9)->nullable();
            $table->string('invoice_address')->nullable();
            $table->string('invoice_postal_code', 20)->nullable();
            $table->string('invoice_city')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn([
                'invoice_company_name',
                'invoice_organization_number',
                'invoice_address',
                'invoice_postal_code',
                'invoice_city',
            ]);
        });
    }
};
