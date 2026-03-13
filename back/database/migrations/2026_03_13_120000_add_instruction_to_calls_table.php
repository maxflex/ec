<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('calls', function (Blueprint $table) {
            // Храним снапшоты instruction/prompt для 2 этапов:
            // transcription (ASR) и analysis (анализ транскрипта).
            $table->json('instruction')->nullable()->after('analysis_3');
        });
    }

    public function down(): void
    {
        Schema::table('calls', function (Blueprint $table) {
            $table->dropColumn('instruction');
        });
    }
};
