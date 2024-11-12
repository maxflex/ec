<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::dropIfExists('macros');
        Schema::create('macros', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('text_ooo')->nullable();
            $table->text('text_ip')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('');
    }
};
