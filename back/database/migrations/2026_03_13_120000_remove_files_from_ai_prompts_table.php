<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Для уже мигрированных БД удаляем legacy-колонку, если она осталась.
        if (! Schema::hasColumn('ai_prompts', 'files')) {
            return;
        }

        Schema::table('ai_prompts', function (Blueprint $table) {
            $table->dropColumn('files');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('ai_prompts', 'files')) {
            return;
        }

        Schema::table('ai_prompts', function (Blueprint $table) {
            $table->json('files')->nullable()->after('prompt');
        });
    }
};
