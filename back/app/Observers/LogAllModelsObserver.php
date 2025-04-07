<?php

namespace App\Observers;

use App\Enums\LogType;
use App\Models\Log;

/**
 * Этот observer вешается на все модели, чтобы логировать все действия в системе
 */
class LogAllModelsObserver
{
    public function created($model)
    {
        Log::add(LogType::create, $model);
    }

    public function updated($model)
    {
        Log::add(LogType::update, $model);
    }

    public function deleted($model)
    {
        Log::add(LogType::delete, $model);
    }
}
