<?php

use App\Enums\Cabinet;
use App\Enums\LessonStatus;
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
            $table->enum('cabinet', collect(Cabinet::cases())->map(fn ($e) => $e->value)->all());
            $table->dateTime('start_at');
            $table->dateTime('conducted_at')->nullable();
            $table->boolean('is_unplanned');
            $table->boolean('is_topic_verified');
            $table->string('topic')->nullable();
            $table->foreignIdFor(User::class)->nullable()->constrained()->nullable();
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
