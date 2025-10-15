<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('macros', function (Blueprint $table) {
            $table->text('text_ano')->nullable();
            $table->timestamps();
        });

        \Illuminate\Support\Facades\DB::table('macros')->update([
            'text_ano' => DB::raw('text_ooo'),
            'created_at' => DB::raw('CURRENT_TIMESTAMP'),
            'updated_at' => DB::raw('CURRENT_TIMESTAMP'),
        ]);
    }

    public function down(): void {}
};
