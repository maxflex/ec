<?php

use App\Enums\ContractLessonStatus;
use App\Models\Contract;
use App\Models\Lesson;
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
        Schema::create('contract_lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Contract::class)->constrained();
            $table->foreignIdFor(Lesson::class)->constrained();
            $table->unsignedInteger('price');
            $table->enum(
                'status',
                collect(ContractLessonStatus::cases())->map(fn ($e) => $e->name)->all()
            );
            $table->unsignedInteger('minutes_late')->nullable();
            $table->boolean('is_remote')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contract_lessons');
    }
};
