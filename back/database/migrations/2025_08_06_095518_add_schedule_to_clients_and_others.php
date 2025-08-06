<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->json('schedule')->nullable();
        });

        Schema::table('groups', function (Blueprint $table) {
            $table->json('schedule')->nullable();
        });

        Schema::table('teachers', function (Blueprint $table) {
            $table->json('schedule')->nullable();
        });
    }
};
