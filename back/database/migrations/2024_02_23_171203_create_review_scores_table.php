<?php

use App\Enums\Program;
use App\Models\WebReview;
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
        Schema::create('web_review_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(WebReview::class)->constrained();
            $table->enum(
                'program',
                collect(Program::cases())->map(fn ($e) => $e->name)->all()
            );
            $table->unsignedSmallInteger('score');
            $table->unsignedSmallInteger('score_max');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('review_scores');
    }
};
