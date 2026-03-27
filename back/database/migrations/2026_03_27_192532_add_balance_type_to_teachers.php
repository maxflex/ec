<?php

use App\Enums\TeacherBalanceType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            $table
                ->enum('balance_type', array_column(TeacherBalanceType::cases(), 'value'))
                ->default(TeacherBalanceType::normal->value)
                ->after('is_published');

        });

        \Illuminate\Support\Facades\DB::table('teachers')
            ->where('is_split_balance', true)
            ->update([
                'balance_type' => TeacherBalanceType::split->value,
            ]);

        Schema::table('teachers', function (Blueprint $table) {
            $table->dropColumn('is_split_balance');
        });
    }
};
