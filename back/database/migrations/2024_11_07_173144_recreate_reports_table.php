<?php

use App\Enums\Program;
use App\Enums\ReportStatus;
use App\Models\Client;
use App\Models\Teacher;
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
        Schema::dropIfExists('reports');
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Teacher::class)->constrained();
            $table->foreignIdFor(Client::class)->constrained();
            $table->unsignedSmallInteger('year')->index();
            $table->enum(
                'program',
                collect(Program::cases())->map(fn($e) => $e->name)->all()
            )->index();
            $table->mediumText('homework_comment')->nullable();
            $table->mediumText('cognitive_ability_comment')->nullable();
            $table->mediumText('knowledge_level_comment')->nullable();
            $table->mediumText('recommendation_comment')->nullable();
            $table->unsignedSmallInteger('price')->nullable();
            $table->unsignedSmallInteger('grade')->nullable();
            $table->enum(
                'status',
                array_column(ReportStatus::cases(), 'value')
            )->default(ReportStatus::new)->index();
            $table->timestamps();
        });
        Schema::table('reports', function (Blueprint $table) {
            $table->index('created_at'); // Add index to the created_at column
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
