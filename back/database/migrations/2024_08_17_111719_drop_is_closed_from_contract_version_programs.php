<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('contract_version_programs', function (Blueprint $table) {
            $table->dropIndex('contract_version_programs_is_closed_index');
            $table->dropColumn('is_closed');
            $table->dropColumn('prices');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contract_version_programs', function (Blueprint $table) {
            //
        });
    }
};
