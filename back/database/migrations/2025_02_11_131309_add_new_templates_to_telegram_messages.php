<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('telegram_messages', function (Blueprint $table) {
            $table->string('template_new')->nullable();
        });

        DB::table('telegram_messages')->update([
            'template_new' => DB::raw('template'),
        ]);

        Schema::table('telegram_messages', function (Blueprint $table) {
            $table->dropColumn('template');
            $table->enum('template', array_column(\App\Enums\TelegramTemplate::cases(), 'value'))
                ->nullable();
        });

        DB::table('telegram_messages')->update([
            'template' => DB::raw('template_new'),
        ]);

        Schema::table('telegram_messages', function (Blueprint $table) {
            $table->dropColumn('template_new');
        });
    }

    public function down(): void
    {
        Schema::table('telegram_messages', function (Blueprint $table) {
            //
        });
    }
};
