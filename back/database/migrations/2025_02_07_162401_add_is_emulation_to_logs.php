<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('logs', function (Blueprint $table) {
            $table->boolean('is_emulation')->default(false)->after('entity_id');
            // возвращено / на проверку
        });
    }

    public function down(): void
    {
        Schema::table('logs', function (Blueprint $table) {
            //
        });
    }
};
