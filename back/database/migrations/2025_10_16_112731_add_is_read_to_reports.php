<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->boolean('is_read')->after('status')->default(false)->index();
        });

        \Illuminate\Support\Facades\DB::table('reports')
            ->where('delivery', 'read')
            ->update(['is_read' => true]);

        Schema::table('reports', function (Blueprint $table) {
            $table->dropColumn('delivery');
        });
    }
};
