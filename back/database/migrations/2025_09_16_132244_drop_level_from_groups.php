<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('groups')->whereNotNull('level')->update([
            'letter' => DB::raw('level'),
        ]);

        Schema::table('groups', function (Blueprint $table) {
            $table->dropColumn('level');
        });
    }
};
