<?php

namespace App\Observers;

use App\Enums\RequestStatus;
use App\Events\MenuCountsUpdatedEvent;
use App\Models\Request as ClientRequest;

class RequestObserver
{
    /**
     * Новая заявка влияет на счетчик только если она сразу создана в статусе new.
     */
    public function created(ClientRequest $request): void
    {
        if ($request->is_new) {
            MenuCountsUpdatedEvent::dispatchRequestsCount();
        }
    }

    /**
     * Дополнительный сценарий против рассинхрона:
     * при удалении заявки в статусе new счетчик обязан уменьшиться.
     */
    public function deleted(ClientRequest $request): void
    {
        if ($request->is_new) {
            MenuCountsUpdatedEvent::dispatchRequestsCount();
        }
    }

    /**
     * На счетчик влияет только переход статуса "в new" или "из new".
     */
    public function updated(ClientRequest $request): void
    {
        if (! $request->wasChanged('status')) {
            return;
        }

        // getOriginal() в Laravel возвращает "сырое" значение из БД до приведения (cast),
        // поэтому для сравнения используем ->value нашего Enum.
        $wasNew = $request->getOriginal('status') === RequestStatus::new->value;

        // Если заявка перешла ИЗ статуса new ИЛИ В статус new
        if ($wasNew || $request->is_new) {
            MenuCountsUpdatedEvent::dispatchRequestsCount();
        }
    }
}
