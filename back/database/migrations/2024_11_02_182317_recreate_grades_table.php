<?php

use App\Enums\Program;
use App\Enums\Quarter;
use App\Models\Client;
use App\Models\Teacher;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('grades');
        Schema::create('grades', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Client::class)->constrained();
            $table->enum(
                'program',
                collect(Program::cases())->map(fn($e) => $e->name)->all()
            )->index();
            $table->unsignedSmallInteger('year')->index();
            $table->enum(
                'quarter',
                collect(Quarter::cases())->map(fn($e) => $e->name)->all()
            )->index();
            $table->unsignedSmallInteger('grade');
            $table->foreignIdFor(Teacher::class)->nullable()->constrained();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grades');
    }
};
