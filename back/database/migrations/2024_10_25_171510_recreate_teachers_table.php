<?php

use App\Enums\Subject;
use App\Enums\TeacherStatus;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('teachers');
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('middle_name')->nullable();
            $table->set(
                'subjects',
                array_column(Subject::cases(), 'value')
            )->nullable();
            $table->enum(
                'status',
                array_column(TeacherStatus::cases(), 'value')
            )->default(TeacherStatus::inactive->name)->index();
            $table->boolean('is_published')->default(false)->index();
            $table->text('desc')->nullable();
            $table->string('photo_desc', 1000)->nullable();
            $table->unsignedSmallInteger('so')->nullable();
            $table->json('passport')->nullable();
            $table->foreignIdFor(User::class)->nullable()->constrained();
            $table->timestamps();
        });
        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teachers');
    }
};
