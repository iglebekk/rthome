<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table): void {
            $table->timestamp('email_sent_at')->nullable();
            $table->unsignedInteger('email_send_attempts')->default(0);
            $table->text('email_last_error')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table): void {
            $table->dropColumn(['email_sent_at', 'email_send_attempts', 'email_last_error']);
        });
    }
};
