<?php

use App\Enums\ClientLessonStatus;
use App\Models\ContractVersionProgram;
use App\Models\Lesson;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('client_lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(ContractVersionProgram::class)->constrained();
            $table->foreignIdFor(Lesson::class)->constrained();
            $table->unsignedInteger('price');
            $table->enum(
                'status',
                array_column(ClientLessonStatus::cases(), 'name')
            );
            $table->json('scores')->nullable()->default(null);
            $table->unsignedInteger('minutes_late')->nullable();
            $table->boolean('is_remote')->default(false);
            $table->unique(['contract_version_program_id', 'lesson_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_lessons');
    }
};
