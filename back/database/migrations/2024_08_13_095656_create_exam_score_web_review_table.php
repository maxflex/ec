<?php

use App\Models\ExamScore;
use App\Models\WebReview;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('exam_score_web_review', function (Blueprint $table) {
            $table->foreignIdFor(ExamScore::class)->constrained();
            $table->foreignIdFor(WebReview::class)->constrained();
            $table->unique(['exam_score_id', 'web_review_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_score_web_review');
    }
};
