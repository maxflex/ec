<?php

use App\Enums\Program;
use App\Models\Client;
use App\Models\Teacher;
use App\Models\User;
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
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Teacher::class)->constrained();
            $table->foreignIdFor(Client::class)->constrained();
            $table->unsignedSmallInteger('year')->index();
            $table->enum(
                'program',
                collect(Program::cases())->map(fn ($e) => $e->name)->all()
            )->index();
            $table->mediumText('homework_comment')->nullable();
            $table->unsignedSmallInteger('price')->nullable();
            $table->boolean('is_published')->default(false);
            $table->boolean('is_moderated')->default(false);
            $table->unsignedBigInteger('moderated_user_id')->nullable()->default(null);
            $table->foreign('moderated_user_id')->references('id')->on('users');
            // $table->foreignIdFor(User::class)->constrained()->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
