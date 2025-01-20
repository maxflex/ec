<?php

use App\Enums\ReportStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->string('status_new');
        });

        DB::table('reports')->update([
            'status_new' => DB::raw('status')
        ]);

        DB::table('reports')
            ->where('status', 'new')
            ->update([
                'status_new' => ReportStatus::draft->value
            ]);

        Schema::table('reports', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->enum('status', array_column(ReportStatus::cases(), 'value'))
                ->default(ReportStatus::draft)
                ->index()
                ->after('grade');
        });

        DB::table('reports')->update([
            'status' => DB::raw('status_new')
        ]);

        Schema::table('reports', function (Blueprint $table) {
            $table->dropColumn('status_new');
        });
    }
};
