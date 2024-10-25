<?php

use App\Enums\Exam;
use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * delete + uncomment create_web_reviews
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('exam_scores');
        Schema::create('exam_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Client::class)->constrained();
            $table->unsignedSmallInteger('year');
            $table->enum(
                'exam',
                array_column(Exam::cases(), 'name')
            );
            $table->unsignedSmallInteger('score');
            $table->unsignedSmallInteger('max_score')->comment('Delete after use');
            $table->foreignIdFor(User::class)->constrained();
            $table->timestamps();
        });
        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::dropIfExists('');
    }
};
