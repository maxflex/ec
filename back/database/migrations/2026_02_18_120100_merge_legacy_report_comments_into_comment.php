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
        // 1) Переносим контент старых полей в единый comment только там, где comment пуст.
        DB::table('reports')
            ->whereNull('comment')
            ->where(function ($query) {
                $query
                    ->whereNotNull('homework_comment')
                    ->orWhereNotNull('cognitive_ability_comment')
                    ->orWhereNotNull('knowledge_level_comment')
                    ->orWhereNotNull('recommendation_comment');
            })
            ->update([
                'comment' => DB::raw("CONCAT(
                    'Выполнение домашнего задания\n', IFNULL(homework_comment, ''),
                    '\n\nСпособность усваивать новый материал\n', IFNULL(cognitive_ability_comment, ''),
                    '\n\nТекущий уровень знаний\n', IFNULL(knowledge_level_comment, ''),
                    '\n\nРекомендации родителям\n', IFNULL(recommendation_comment, '')
                )"),
            ]);

        // 2) Удаляем старые колонки.
        Schema::table('reports', function (Blueprint $table) {
            $table->dropColumn([
                'homework_comment',
                'cognitive_ability_comment',
                'knowledge_level_comment',
                'recommendation_comment',
            ]);
        });
    }
};
