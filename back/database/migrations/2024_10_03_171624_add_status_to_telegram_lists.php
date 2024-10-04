<?php

use App\Enums\TelegramListStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('telegram_lists', function (Blueprint $table) {
            $table->dropColumn('is_sent');

            $table->enum(
                'status',
                collect(TelegramListStatus::cases())->map(fn($e) => $e->name)->all()
            )
                ->after('text')
                ->default(TelegramListStatus::scheduled->name)
                ->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('telegram_lists', function (Blueprint $table) {
            //
        });
    }
};
