<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('telegram_messages', function (Blueprint $table) {
            $table->string('template_new')->nullable()->index();
        });
        \Illuminate\Support\Facades\DB::table('telegram_messages')->update([
            'template_new' => DB::raw('template'),
        ]);
        Schema::table('telegram_messages', function (Blueprint $table) {
            $table->dropColumn('template');
        });
        Schema::table('telegram_messages', function (Blueprint $table) {
            $table->renameColumn('template_new', 'template');
        });
    }
};
