<?php

use App\Models\Teacher;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('head_teacher_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Teacher::class)->constrained();
            $table->unsignedSmallInteger('year')->index();
            $table->unsignedSmallInteger('month');
            $table->text('text');
            $table->timestamps();
            $table->unique(['teacher_id', 'year', 'month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('head_teacher_reports');
    }
};
