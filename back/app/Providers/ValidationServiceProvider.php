<?php

namespace App\Providers;

use App\Models\Phone;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;
use App\Utils\Phone as UtilsPhone;

class ValidationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        Validator::extend('phone', fn ($attr, $number, $params) => UtilsPhone::validate($number));
        Validator::extend('mobile', fn ($attr, $number, $params) => UtilsPhone::validate($number, true, true));
        Validator::extend(
            'no_duplicates',
            function ($attr, $number, $params) {
                [$entityId, $entityType] = $params;
                return !Phone::query()
                    ->whereNumber($number)
                    ->when($entityId, fn ($q) => $q->where('entity_id', '<>', $entityId))
                    ->where('entity_type', $entityType)
                    ->exists();
            }
        );
    }
}
