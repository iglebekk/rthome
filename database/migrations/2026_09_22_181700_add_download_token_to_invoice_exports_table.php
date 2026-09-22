<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('invoice_exports', function (Blueprint $table): void {
            $table->string('download_token', 64)->nullable()->unique()->after('id');
        });

        foreach (DB::table('invoice_exports')->whereNull('download_token')->pluck('id') as $id) {
            DB::table('invoice_exports')->where('id', $id)->update(['download_token' => Str::random(64)]);
        }

        Schema::table('invoice_exports', function (Blueprint $table): void {
            $table->string('download_token', 64)->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoice_exports', function (Blueprint $table): void {
            $table->dropUnique(['download_token']);
            $table->dropColumn('download_token');
        });
    }
};
