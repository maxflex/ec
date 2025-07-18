<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->json('mark_sheet')->nullable();
            $table->unsignedTinyInteger('grade')->nullable()->default(null);
        });
    }
};
