<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Можно удалять
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->time('time')->after('quarter')->nullable();
            $table->date('date')->after('quarter')->nullable();
        });

        DB::table('lessons')->update([
            'date' => DB::raw('date(start_at)'),
            'time' => DB::raw('time(start_at)'),
        ]);


        Schema::table('lessons', function (Blueprint $table) {
            $table->dropColumn('start_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            //
        });
    }
};
