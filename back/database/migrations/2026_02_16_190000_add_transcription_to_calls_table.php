<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('calls', function (Blueprint $table) {
            // Поле recording убрано, поэтому добавляем текстовые поля после has_recording.
            $table->text('summary')->nullable()->after('has_recording');
            $table->text('transcription')->nullable()->after('has_recording');
        });
    }

    public function down(): void
    {
        Schema::table('calls', function (Blueprint $table) {
            $table->dropColumn('transcription');
        });
    }
};
