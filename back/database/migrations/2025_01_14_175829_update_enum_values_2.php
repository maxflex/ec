<?php

use App\Enums\Program;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tables = [
            'groups',
            'client_reviews',
            'client_tests',
            'tests',
            'reports',
            'grades',
            'scholarship_scores',
            'contract_version_programs'
        ];

        foreach ($tables as $t) {
            Schema::table($t, function (Blueprint $table) {
                $table->dropColumn('program');
            });
            Schema::table($t, function (Blueprint $table) {
                $table->enum(
                    'program',
                    array_column(Program::cases(), 'name')
                )
                    ->nullable()
                    ->index();
            });
            \Illuminate\Support\Facades\DB::table($t)->update([
                'program' => DB::raw('program_new')
            ]);
            Schema::table($t, function (Blueprint $table) {
                $table->dropColumn('program_new');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
