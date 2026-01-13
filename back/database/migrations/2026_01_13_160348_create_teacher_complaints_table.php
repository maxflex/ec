<?php

use App\Enums\TeacherComplaintStatus;
use App\Models\Teacher;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_complaints', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Teacher::class)->constrained();
            $table->text('text');
            $table->enum('status', array_column(TeacherComplaintStatus::cases(), 'value'))
                ->default(TeacherComplaintStatus::new->value)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_complaints');
    }
};
