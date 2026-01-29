<?php

use App\Models\Teacher;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_acts', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Teacher::class)->constrained('teachers');
            $table->unsignedSmallInteger('year')->index();
            $table->date('date');
            $table->date('date_from')->nullable();
            $table->date('date_to')->nullable();
            $table->json('data');
            $table->foreignIdFor(\App\Models\User::class)->constrained('users');
            $table->timestamps();
        });
    }
};
