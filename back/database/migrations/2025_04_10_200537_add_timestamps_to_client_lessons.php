<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('client_lessons', function (Blueprint $table) {
            $table->timestamps();
        });

        \Illuminate\Support\Facades\DB::table('client_lessons', 'cl')
            ->join('lessons as l', 'l.id', '=', 'cl.lesson_id')
            ->update([
                'cl.created_at' => DB::raw('l.conducted_at'),
                'cl.updated_at' => DB::raw('l.conducted_at'),
            ]);
    }
};
