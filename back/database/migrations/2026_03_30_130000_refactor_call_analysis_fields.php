<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Переводим старую основную колонку анализа в новое имя.
        DB::statement('ALTER TABLE `calls` CHANGE `analysis_1` `analysis` TEXT NULL');

        // Удаляем больше неиспользуемые колонки.
        DB::statement('ALTER TABLE `calls` DROP COLUMN `analysis_2`');
        DB::statement('ALTER TABLE `calls` DROP COLUMN `analysis_3`');
    }

    public function down(): void
    {
        // Возвращаем прежнее имя основной колонки.
        DB::statement('ALTER TABLE `calls` CHANGE `analysis` `analysis_1` TEXT NULL');

        // Восстанавливаем удалённые колонки (данные в них не восстанавливаются).
        DB::statement('ALTER TABLE `calls` ADD COLUMN `analysis_2` TEXT NULL AFTER `analysis_1`');
        DB::statement('ALTER TABLE `calls` ADD COLUMN `analysis_3` TEXT NULL AFTER `analysis_2`');
    }
};
