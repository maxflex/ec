<?php

namespace App\Providers;

use App\Observers\LogAllModelsObserver;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Blade;
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
        Carbon::setLocale(config('app.locale'));
        JsonResource::withoutWrapping();
        $this->registerBladeDirectives();
        $this->logAllModels();
    }

    private function registerBladeDirectives(): void
    {
        // Импорт AI-шаблона из БД строго по ID:
        // @import(2, [...])
        Blade::directive('import', function (string $expression): string {
            // Передаем текущую область видимости, чтобы @import вел себя как @include.
            return "<?php echo app(\\App\\Utils\\AI\\AiPromptRenderer::class)->renderByIdWithScope({$expression}, get_defined_vars()); ?>";
        });
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
