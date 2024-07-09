<?php

use App\Enums\Program;
use App\Enums\ScoreType;
use App\Models\Client;
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
        Schema::create('scores', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Client::class)->constrained();
            $table->enum(
                'program',
                collect(Program::cases())->map(fn ($e) => $e->name)->all()
            );
            $table->unsignedSmallInteger('year');
            $table->enum(
                'type',
                collect(ScoreType::cases())->map(fn ($e) => $e->name)->all()
            );
            $table->unsignedSmallInteger('score');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scores');
    }
};
