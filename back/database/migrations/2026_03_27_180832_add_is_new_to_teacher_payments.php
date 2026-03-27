<?php

use App\Enums\TeacherPaymentMethod;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teacher_payments', function (Blueprint $table) {
            $table->boolean('is_new')->default(false);
        });

        \Illuminate\Support\Facades\DB::table('teacher_payments')
            ->where('year', 2025)
            ->where('method', TeacherPaymentMethod::card->value)
            ->update([
                'is_new' => true,
            ]);
    }
};
