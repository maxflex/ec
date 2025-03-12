<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_scores', function (Blueprint $table) {
            $table->boolean('is_published')->default(false)->after('max_score');
        });
        DB::table('exam_scores')
            ->whereRaw('exists (
                select 1 from exam_score_web_review es_wr
                where es_wr.exam_score_id = exam_scores.id
            )')
            ->update(['is_published' => true]);
    }

    public function down(): void {}
};
