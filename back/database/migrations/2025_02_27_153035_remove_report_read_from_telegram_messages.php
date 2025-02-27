<?php

use App\Enums\TelegramTemplate;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        \Illuminate\Support\Facades\DB::table('telegram_messages')
            ->where('template', TelegramTemplate::reportRead)
            ->whereNull('telegram_id')
            ->delete();
    }
};
