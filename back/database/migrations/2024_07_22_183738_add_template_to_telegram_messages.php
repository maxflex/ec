<?php

use App\Enums\TelegramTemplate;
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
        Schema::table('telegram_messages', function (Blueprint $table) {
            $table->enum(
                'template',
                collect(TelegramTemplate::cases())->map(fn ($e) => $e->name)->all()
            )->nullable()->default(null)->after('entry_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('telegram_messages', function (Blueprint $table) {
            //
        });
    }
};
