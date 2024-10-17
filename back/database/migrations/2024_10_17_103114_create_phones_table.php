<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('phones');
        Schema::create('phones', function (Blueprint $table) {
            $table->id();
            $table->string('number')->index();
            $table->string('comment')->nullable();
            $table->morphs('entity');
            $table->unsignedBigInteger('telegram_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phones');
    }
};
