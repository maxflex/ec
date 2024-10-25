<?php

use App\Enums\Exam;
use App\Enums\Program;
use App\Models\Client;
use App\Models\Teacher;
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
        Schema::dropIfExists('web_reviews');
        Schema::create('web_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Client::class)->constrained();
            $table->text('text');
            $table->string('signature');
            $table->unsignedSmallInteger('rating');
            $table->foreignIdFor(User::class)->nullable()->constrained();
            $table->timestamps();
        });

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
            $table->foreignIdFor(User::class)->nullable()->constrained();
            $table->timestamps();
        });

        Schema::dropIfExists('client_reviews');
        Schema::create('client_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Client::class)->constrained();
            $table->foreignIdFor(Teacher::class)->constrained();
            $table->enum(
                'program',
                collect(Program::cases())->map(fn($e) => $e->name)->all()
            );
            $table->text('text');
            $table->unsignedSmallInteger('rating');
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
        Schema::dropIfExists('web_reviews');
    }
};
