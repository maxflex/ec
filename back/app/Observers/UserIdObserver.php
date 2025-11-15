<?php

namespace App\Observers;

use Illuminate\Database\Eloquent\Model;

/**
 * Устанавливает user_id при создании модели
 */
class UserIdObserver
{
    public function creating(Model $model)
    {
        if (auth()->check()) {
            $model->setAttribute('user_id', auth()->id());
        }
    }
}
