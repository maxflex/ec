<?php

use App\Enums\Program;
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
        Schema::create('tests', function (Blueprint $table) {
            $table->id();
            $table->enum(
                'program',
                collect(Program::cases())->map(fn ($e) => $e->name)->all()
            )->nullable();
            $table->string('name');
            $table->string('file')->nullable();
            $table->integer('minutes');
            $table->json('answers')->nullable();
            $table->json('results')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tests');
    }
};
