<?php

use App\Enums\Grade;
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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('middle_name')->nullable();
            $table->set(
                'branches',
                collect(Grade::cases())->map(fn ($e) => $e->name)->all()
            )->nullable();
            $table->foreignIdFor(User::class)->nullable()->constrained();
            $table->date('birthdate')->nullable();
            $table->unsignedBigInteger('head_teacher_id')->nullable()->constrained();
            $table->foreign('head_teacher_id')->references('id')->on('teachers');
            $table->string('parent_first_name')->nullable();
            $table->string('parent_last_name')->nullable();
            $table->string('parent_middle_name')->nullable();
            $table->string('passport_series')->nullable();
            $table->string('passport_number')->nullable();
            $table->string('passport_address')->nullable();
            $table->string('passport_code')->nullable();
            $table->date('passport_issued_date')->nullable();
            $table->string('passport_issued_by')->nullable();
            $table->string('real_address')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
