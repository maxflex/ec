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
        if ($this->isNewStatus($request->status)) {
            MenuCountsUpdatedEvent::dispatchRequestsCount();
        }
    }

    private function isNewStatus(RequestStatus|string|null $status): bool
    {
        return $status === RequestStatus::new
            || $status === RequestStatus::new->value;
    }

    /**
     * На счетчик влияет только переход статуса "в new" или "из new".
     */
    public function updated(ClientRequest $request): void
    {
        if (! $request->wasChanged('status')) {
            return;
        }

        $oldStatus = $request->getOriginal('status');
        $newStatus = $request->status;

        if ($this->isNewStatus($oldStatus) !== $this->isNewStatus($newStatus)) {
            MenuCountsUpdatedEvent::dispatchRequestsCount();
        }
    }

    /**
     * Дополнительный сценарий против рассинхрона:
     * при удалении заявки в статусе new счетчик обязан уменьшиться.
     */
    public function deleted(ClientRequest $request): void
    {
        if ($this->isNewStatus($request->status)) {
            MenuCountsUpdatedEvent::dispatchRequestsCount();
        }
    }
}
