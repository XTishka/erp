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
        Schema::table('company_profiles', function (Blueprint $table) {
            $table->string('name')->default('Default')->after('company_id');
            $table->boolean('is_default')->default(false)->after('entity_type')->index();
            $table->unique(['company_id', 'name'], 'company_profiles_company_id_name_unique');
        });

        DB::table('company_profiles')->update([
            'name' => 'Default',
            'is_default' => true,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('company_profiles', function (Blueprint $table) {
            $table->dropUnique('company_profiles_company_id_name_unique');
            $table->dropColumn(['name', 'is_default']);
        });
    }
};
