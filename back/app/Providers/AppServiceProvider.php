<?php

namespace App\Providers;

use App\Observers\LogAllModelsObserver;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\ServiceProvider;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use TelegramBot\Api\BotApi;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->instance('Telegram', new BotApi(config('telegram.key')));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        JsonResource::withoutWrapping();
        $this->logAllModels();
    }

    private function logAllModels()
    {
        $path = realpath(app_path().'/Models');
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path)) as $filename) {
            if ($filename->isFile()) {
                $class = mb_strimwidth($filename->getRealPath(), strlen($path) + 1, -4);
                $class = str_replace('/', '\\', $class);
                $class = '\\App\\Models\\'.$class;
                if (! defined($class.'::DISABLE_LOGS')) {
                    $class::observe(LogAllModelsObserver::class);
                }
            }
        }
    }
}
