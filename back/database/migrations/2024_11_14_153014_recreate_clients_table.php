<?php

use App\Enums\Branch;
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
        Schema::dropIfExists('clients');
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('middle_name')->nullable();
            $table->set(
                'branches',
                collect(Branch::cases())->map(fn($e) => $e->name)->all()
            )->nullable();
            $table->unsignedBigInteger('head_teacher_id')->nullable();
            $table->foreign('head_teacher_id')->references('id')->on('teachers');
            $table->boolean('is_remote')->default(false);
            $table->json('passport')->nullable();
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
        Schema::dropIfExists('clients');
    }
};
