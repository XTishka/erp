<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tables = [
            'document_defaults',
            'estimates',
            'invoices',
            'recurring_invoices',
            'bills',
        ];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->foreignId('company_profile_id')
                    ->nullable()
                    ->after('company_id')
                    ->constrained('company_profiles')
                    ->nullOnDelete();
            });
        }

        $defaultProfiles = DB::table('company_profiles')
            ->select('id', 'company_id')
            ->where('is_default', true)
            ->pluck('id', 'company_id');

        foreach ($tables as $tableName) {
            DB::table($tableName)
                ->orderBy('id')
                ->chunkById(100, function ($records) use ($defaultProfiles, $tableName) {
                    foreach ($records as $record) {
                        $profileId = $defaultProfiles[$record->company_id] ?? null;

                        if ($profileId === null) {
                            continue;
                        }

                        DB::table($tableName)
                            ->where('id', $record->id)
                            ->update(['company_profile_id' => $profileId]);
                    }
                });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'document_defaults',
            'estimates',
            'invoices',
            'recurring_invoices',
            'bills',
        ];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropConstrainedForeignId('company_profile_id');
            });
        }
    }
};
