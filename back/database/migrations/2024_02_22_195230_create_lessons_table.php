<?php

use App\Enums\Cabinet;
use App\Enums\LessonStatus;
use App\Enums\Quarter;
use App\Models\Group;
use App\Models\Teacher;
use App\Models\User;
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
        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Group::class)->constrained();
            $table->foreignIdFor(Teacher::class)->nullable()->constrained();
            $table->unsignedInteger('price');
            $table->enum('status', collect(LessonStatus::cases())->map(fn ($e) => $e->name)->all());
            $table->enum('cabinet', collect(Cabinet::cases())->map(fn($e) => $e->value)->all())->nullable();
            $table->enum(
                'quarter',
                array_column(Quarter::cases(), 'name'),
            )->nullable()->default(null)->index();

            $table->date('date');
            $table->time('time');

            $table->dateTime('conducted_at')->nullable();
            $table->boolean('is_free')->default(false);
            $table->boolean('is_unplanned')->default(false);
            $table->boolean('is_topic_verified')->default(false);
            $table->string('topic', 1000)->nullable();
            $table->string('homework', 1000)->nullable();
            $table->json('files')->nullable();
            $table->foreignIdFor(User::class)->nullable()->constrained();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
};
