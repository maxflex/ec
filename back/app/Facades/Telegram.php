<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;
use TelegramBot\Api\BotApi;

/**
 * @mixin BotApi
 */
class Telegram extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'Telegram';
    }
}
