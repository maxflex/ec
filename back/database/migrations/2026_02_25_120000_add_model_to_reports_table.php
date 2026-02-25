<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->string('model')->nullable()->after('ai_comment');
        });

        \Illuminate\Support\Facades\DB::table('reports')
            ->whereNotNull('ai_comment')
            ->update([
                'model' => 'gemini-3-flash-preview',
            ]);
    }

    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropColumn('model');
        });
    }
};
