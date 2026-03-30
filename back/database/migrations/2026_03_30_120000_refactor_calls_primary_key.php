<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1) Переносим старый Mango-id из calls.id в calls.entry_id.
        DB::statement('ALTER TABLE `calls` CHANGE `id` `entry_id` VARCHAR(128) NOT NULL');

        // 2) Освобождаем primary key, чтобы назначить его на стандартный автоинкрементный id.
        DB::statement('ALTER TABLE `calls` DROP PRIMARY KEY');

        // 3) Добавляем новый стандартный id первым столбцом.
        DB::statement('ALTER TABLE `calls` ADD COLUMN `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST');

        // 4) Сохраняем уникальность Mango entry_id.
        DB::statement('ALTER TABLE `calls` ADD UNIQUE INDEX `calls_entry_id_unique` (`entry_id`)');
    }

    public function down(): void
    {
        // Откат к старой схеме: строковый id как primary key.
        DB::statement('ALTER TABLE `calls` DROP PRIMARY KEY');
        DB::statement('ALTER TABLE `calls` DROP INDEX `calls_entry_id_unique`');
        DB::statement('ALTER TABLE `calls` DROP COLUMN `id`');
        DB::statement('ALTER TABLE `calls` CHANGE `entry_id` `id` VARCHAR(128) NOT NULL');
        DB::statement('ALTER TABLE `calls` ADD PRIMARY KEY (`id`)');
    }
};
