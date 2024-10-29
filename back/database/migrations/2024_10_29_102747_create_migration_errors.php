<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('migration_errors', function (Blueprint $table) {
            $table->id();
            $table->string('new_table')->index();
            $table->string('old_table')->index();
            $table->unsignedInteger('new_id')->index();
            $table->unsignedInteger('old_id')->index();
            $table->string('message', 1000);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('migration_errors');
    }
};
