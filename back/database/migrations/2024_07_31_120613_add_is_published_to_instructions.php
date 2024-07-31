<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('instructions', function (Blueprint $table) {
            $table->boolean('is_published')->after('text')->default(false)->index();
        });
        DB::table('instructions')->update([
            'is_published' => 1
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('instructions', function (Blueprint $table) {
            //
        });
    }
};
