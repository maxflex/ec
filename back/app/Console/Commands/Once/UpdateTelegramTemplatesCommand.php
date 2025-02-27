<?php

namespace App\Console\Commands\Once;

use App\Enums\TelegramTemplate;
use Illuminate\Console\Command;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdateTelegramTemplatesCommand extends Command
{
    protected $signature = 'once:update-telegram-templates';

    protected $description = 'Обновить enum для TelegramTemplate';

    public function handle(): void
    {
        $this->line('Updating telegram templates...');

        DB::table('telegram_messages')
            ->where('template', 'firstLogin')
            ->delete();

        Schema::table('telegram_messages', function (Blueprint $table) {
            $table->string('template_new')->nullable();
        });

        DB::table('telegram_messages')->update([
            'template_new' => DB::raw('template'),
        ]);

        Schema::table('telegram_messages', function (Blueprint $table) {
            $table->dropColumn('template');
            $table->enum('template', array_column(TelegramTemplate::cases(), 'value'))
                ->nullable();
        });

        DB::table('telegram_messages')->update([
            'template' => DB::raw('template_new'),
        ]);

        Schema::table('telegram_messages', function (Blueprint $table) {
            $table->dropColumn('template_new');
        });

        $this->info('Telegram templates updated');
    }
}
