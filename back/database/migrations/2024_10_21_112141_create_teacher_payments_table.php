<?php

use App\Enums\TeacherPaymentMethod;
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
        Schema::create('teacher_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('sum');
            $table->date('date')->index();
            $table->unsignedSmallInteger('year')->index();
            $table->enum(
                'method',
                collect(TeacherPaymentMethod::cases())->map(
                    fn ($e) => $e->name
                )->all()
            );
            $table->string('purpose')->nullable();
            $table->boolean('is_confirmed')->default(false);
            $table->foreignIdFor(Teacher::class)->constrained();
            $table->foreignIdFor(User::class)->constrained();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_payments');
    }
};
