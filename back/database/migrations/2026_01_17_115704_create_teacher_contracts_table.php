<?php

use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Teacher::class)->constrained('teachers');
            $table->unsignedSmallInteger('year')->index();
            $table->date('date');
            $table->boolean('is_active');
            $table->json('data');
            $table->json('file')->nullable();
            $table->foreignIdFor(User::class)->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_contracts');
    }
};
