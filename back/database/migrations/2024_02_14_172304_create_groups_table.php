<?php

use App\Enums\Grade;
use App\Enums\Subject;
use App\Models\Teacher;
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
        Schema::create('groups', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Teacher::class)->constrained();
            $table->enum(
                'subject',
                collect(Subject::cases())->map(fn ($e) => $e->name)->all()
            )->index();
            $table->enum(
                'grade',
                collect(Grade::cases())->map(fn ($e) => $e->name)->all()
            )->index();
            $table->unsignedSmallInteger('year');
            $table->boolean('is_archived')->index()->default(false);
            $table->json('zoom')->nullable();
            $table->unsignedSmallInteger('lessons_planned')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('groups');
    }
};
