<?php

use App\Enums\Exam;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $exams = array_column(Exam::cases(), "name");
        Schema::create('exam_dates', function (Blueprint $table) use ($exams) {
            $table->id();
            $table->enum('exam', $exams)->index()->unique();
            $table->json('dates')->nullable();
        });
        foreach ($exams as $exam) {
            DB::table('exam_dates')->insert([
                'exam' => $exam
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_dates');
    }
};
