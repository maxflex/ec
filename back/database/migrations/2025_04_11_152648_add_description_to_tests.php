<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tests', function (Blueprint $table) {
            $table->string('description')->nullable()->after('name');
        });
        Schema::table('client_tests', function (Blueprint $table) {
            $table->string('description')->nullable()->after('name');
        });
    }
};
