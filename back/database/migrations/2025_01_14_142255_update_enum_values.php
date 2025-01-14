<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration {
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
                $table->string('program_new')->index();
            });
            DB::table($t)->update([
                'program_new' => DB::raw('program')
            ]);
        }
    }

    public function down()
    {

    }
};
