<?php

use App\Enums\Program;
use App\Models\Client;
use App\Models\Test;
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
        Schema::create('client_tests', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Client::class)->constrained();
            // $table->foreignIdFor(Test::class);
            $table->enum(
                'program',
                collect(Program::cases())->map(fn ($e) => $e->name)->all()
            );
            $table->string('name');
            $table->string('file')->nullable();
            $table->integer('minutes');
            $table->json('questions');
            $table->json('answers')->nullable();
            $table->dateTime('started_at')->nullable();
            $table->dateTime('finished_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_tests');
    }
};
