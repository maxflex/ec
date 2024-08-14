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
                collect(ClientLessonStatus::cases())->map(fn($e) => $e->name)->all()
            );
            $table->unsignedInteger('minutes_late')->nullable();
            $table->boolean('is_remote')->default(false);
            $table->unique(['contract_version_program_id', 'lesson_id']);
        });
        Schema::dropIfExists('contract_lessons');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_lessons');
    }
};
