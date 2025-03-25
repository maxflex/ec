<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('telegram_messages', function (Blueprint $table) {
            $table->string('extra')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::table('telegram_messages', function (Blueprint $table) {
            $table->dropColumn('extra');
        });
    }
};
