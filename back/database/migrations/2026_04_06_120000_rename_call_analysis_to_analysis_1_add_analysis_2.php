<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Переносим текущее поле анализа в версионированный ключ analysis_1.
        if (Schema::hasColumn('calls', 'analysis') && ! Schema::hasColumn('calls', 'analysis_1')) {
            DB::statement('ALTER TABLE `calls` CHANGE `analysis` `analysis_1` TEXT NULL');
        }

        // analysis_2 добавляем как nullable и оставляем null по умолчанию.
        if (! Schema::hasColumn('calls', 'analysis_2')) {
            DB::statement('ALTER TABLE `calls` ADD COLUMN `analysis_2` TEXT NULL AFTER `analysis_1`');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('calls', 'analysis_2')) {
            DB::statement('ALTER TABLE `calls` DROP COLUMN `analysis_2`');
        }

        if (Schema::hasColumn('calls', 'analysis_1') && ! Schema::hasColumn('calls', 'analysis')) {
            DB::statement('ALTER TABLE `calls` CHANGE `analysis_1` `analysis` TEXT NULL');
        }
    }
};
