<?php

use App\Models\ClientLesson;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('violations', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Lesson::class)->constrained('lessons');
            $table->foreignIdFor(ClientLesson::class)->nullable()->constrained();
            $table->boolean('is_resolved')->index();
            $table->json('photo')->nullable();
            $table->json('video')->nullable();
            $table->foreignIdFor(User::class)->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('violations');
    }
};
