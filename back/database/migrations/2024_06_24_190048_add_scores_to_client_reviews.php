<?php

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
        Schema::table('client_reviews', function (Blueprint $table) {
            $table->unsignedSmallInteger('max_score')->nullable()->after('rating');
            $table->unsignedSmallInteger('score')->nullable()->after('rating');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('client_reviews', function (Blueprint $table) {
            $table->dropColumn('score');
            $table->dropColumn('max_score');
        });
    }
};
