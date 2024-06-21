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
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_active')->after('middle_name')->index()->default(false);
        });
        DB::table('users')
            ->whereIn('id', [1, 5, 140, 12, 223, 220, 212, 209, 197])
            ->update([
                'is_active' => 1
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Schema::table('users', function (Blueprint $table) {
        //     $table->dropIndex('is_active_index');
        // });
        // Schema::table('users', function (Blueprint $table) {
        //     $table->dropColumn('is_active');
        // });
    }
};
