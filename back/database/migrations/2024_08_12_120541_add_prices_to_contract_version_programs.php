<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('contract_version_programs', function (Blueprint $table) {
            $table->json('prices')->after('lessons_planned')->nullable();
        });
        DB::update(<<<SQL
            update contract_version_programs
            set prices = concat('[[', `lessons`, ', ', `price`, ']]');
        SQL
        );
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
