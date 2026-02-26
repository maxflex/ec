<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('reports', 'model') && ! Schema::hasColumn('reports', 'ai_model')) {
            Schema::table('reports', function (Blueprint $table) {
                // Переименовываем поле модели, чтобы оно не конфликтовало с generic-термином model.
                $table->renameColumn('model', 'ai_model');
            });
        }

        if (! Schema::hasColumn('reports', 'ai_instruction')) {
            Schema::table('reports', function (Blueprint $table) {
                // Снимок фактического system instruction + user prompt в момент генерации.
                $table->text('ai_instruction')->nullable()->after('ai_model');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('reports', 'ai_instruction')) {
            Schema::table('reports', function (Blueprint $table) {
                $table->dropColumn('ai_instruction');
            });
        }

        if (Schema::hasColumn('reports', 'ai_model') && ! Schema::hasColumn('reports', 'model')) {
            Schema::table('reports', function (Blueprint $table) {
                $table->renameColumn('ai_model', 'model');
            });
        }
    }
};
