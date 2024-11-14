<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pass_logs', function (Blueprint $table) {
            $table->id();
            $table->morphs('entity');
            $table->string('comment');
            $table->string('complaint')->nullable();
            $table->dateTime('used_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pass_logs');
    }
};
