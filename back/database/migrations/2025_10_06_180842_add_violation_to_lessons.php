<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->string('violation_comment')->nullable()->default(null)->after('files');
            $table->boolean('is_violation')->nullable()->default(null)->after('files');
        });
    }
};
