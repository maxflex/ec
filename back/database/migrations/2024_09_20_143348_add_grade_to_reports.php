<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->unsignedSmallInteger('grade')->nullable()->after('price');
            $table->mediumText('cognitive_ability_comment')->nullable()->after('homework_comment');
            $table->mediumText('knowledge_level_comment')->nullable()->after('homework_comment');
            $table->mediumText('recommendation_comment')->nullable()->after('homework_comment');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            //
        });
    }
};
