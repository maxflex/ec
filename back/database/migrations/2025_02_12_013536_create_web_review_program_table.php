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
        Schema::create('web_review_program', function (Blueprint $table) {
            $table->foreignIdFor(WebReview::class)->constrained('web_reviews');
            $table->enum('program', array_column(Program::cases(), 'value'))->index();
            $table->primary(['web_review_id', 'program']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('web_review_program');
    }
};
