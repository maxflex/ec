<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('complaints', function (Blueprint $table) {
            $table->boolean('is_resolved')->default(false)->after('program')->index();
        });
        \Illuminate\Support\Facades\DB::table('complaints')->update(['is_resolved' => true]);
    }
};
