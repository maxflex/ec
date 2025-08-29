<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('logs', function (Blueprint $table) {
            $table->unsignedBigInteger('telegram_id')->nullable()->after('entity_id');
            $table->string('number')->nullable()->after('entity_id');
        });
    }
};
