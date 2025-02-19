<?php

use App\Enums\Program;
use App\Models\WebReview;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('web_review_program');
        Schema::create('web_review_programs', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(WebReview::class)->constrained()->cascadeOnDelete();
            $table->enum('program', array_column(Program::cases(), 'value'))->index();
            $table->unique(['web_review_id', 'program']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('web_review_programs');
    }
};
