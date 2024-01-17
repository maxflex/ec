<?php

use App\Enums\Subject;
use App\Enums\TeacherStatus;
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
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('middle_name')->nullable();
            $table->set(
                'subjects',
                collect(Subject::cases())->map(fn ($e) => $e->name)->all()
            )->nullable();
            $table->enum(
                'status',
                collect(TeacherStatus::cases())->map(fn ($e) => $e->name)->all()
            )->default(TeacherStatus::inactive->name)->index();
            $table->text('desc')->nullable();
            $table->string('photo_desc', 1000)->nullable();
            $table->string('passport_series')->nullable();
            $table->string('passport_number')->nullable();
            $table->string('passport_address')->nullable();
            $table->string('passport_code')->nullable();
            $table->string('passport_issued_by')->nullable();
            $table->unsignedSmallInteger('so')->nullable();
            $table->timestamps();
        });

        // 'passport_series', 'passport_number',
        // 'passport_code', 'passport_issue_place', 'passport_address'
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teachers');
    }
};
