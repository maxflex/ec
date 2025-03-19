<?php

use App\Enums\InstructionStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('instructions', function (Blueprint $table) {
            $table
                ->enum('status', array_column(InstructionStatus::cases(), 'value'))
                ->default(InstructionStatus::draft->value)
                ->index();
        });
        \Illuminate\Support\Facades\DB::table('instructions')
            ->where('is_published', true)
            ->update(['status' => InstructionStatus::published->value]);
        Schema::table('instructions', function (Blueprint $table) {
            $table->dropColumn('is_published');
        });
    }
};
