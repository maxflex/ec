<?php

namespace App\Utils;

use App\Models\Call;
use App\Models\Client;
use App\Models\Comment;
use App\Models\Phone;
use App\Models\Representative;
use App\Utils\AI\CallAnalysisService;

class CallCommentSuggest
{
    /**
     * Ищем последний звонок менеджера,
     * номер которого присутствует у текущей сущности
     * (для пары Client/Representative учитываем телефоны обеих сторон).
     */
    public static function findLastCallForEntity(int $userId, string $entityType, int $entityId): ?Call
    {
        $latestCall = self::findLatestUserCall($userId);
        if (! $latestCall) {
            return null;
        }

        // Для коротких звонков (< 10 сек) AI-анализ не запускается, suggest не ждём.
        if (! CallAnalysisService::shouldAnalyze($latestCall)) {
            return null;
        }

        if (! self::entityHasCallNumber($entityType, $entityId, $latestCall->number)) {
            return null;
        }

        if (self::hasAnyCommentAfterCall($userId, $entityType, $entityId, $latestCall->created_at)) {
            return null;
        }

        return $latestCall;
    }

    /**
     * Последний сохранённый звонок менеджера.
     * Важно: берём по внутреннему PK id, а не по created_at (дата может приходить "не по порядку").
     */
    public static function findLatestUserCall(int $userId): ?Call
    {
        return Call::query()
            ->where('user_id', $userId)
            ->latest('id')
            ->first();
    }

    /**
     * Проверка: после звонка уже есть комментарий по этой сущности от этого пользователя.
     */
    public static function hasAnyCommentAfterCall(
        int $userId,
        string $entityType,
        int $entityId,
        string $callCreatedAt
    ): bool {
        return Comment::query()
            ->where('user_id', $userId)
            ->where('entity_type', $entityType)
            ->where('entity_id', $entityId)
            ->where('created_at', '>=', $callCreatedAt)
            ->exists();
    }

    /**
     * Есть ли у сущности телефон, совпадающий с номером звонка.
     */
    private static function entityHasCallNumber(string $entityType, int $entityId, string $number): bool
    {
        // Базовая проверка: номер принадлежит самой сущности.
        if (self::entityOwnsNumber($entityType, $entityId, $number)) {
            return true;
        }

        // Для клиента учитываем также телефоны его представителей.
        if ($entityType === Client::class) {
            return self::clientRepresentativeOwnsNumber($entityId, $number);
        }

        // Для представителя учитываем также телефоны связанного клиента.
        if ($entityType === Representative::class) {
            return self::representativeClientOwnsNumber($entityId, $number);
        }

        return false;
    }

    /**
     * Номер напрямую привязан к этой сущности.
     */
    private static function entityOwnsNumber(string $entityType, int $entityId, string $number): bool
    {
        return Phone::query()
            ->where('entity_type', $entityType)
            ->where('entity_id', $entityId)
            ->where('number', $number)
            ->exists();
    }

    /**
     * Номер привязан к представителю ученика.
     */
    private static function clientRepresentativeOwnsNumber(int $clientId, string $number): bool
    {
        return Phone::query()
            ->where('entity_type', Representative::class)
            ->whereIn(
                'entity_id',
                Representative::query()
                    ->select('id')
                    ->where('client_id', $clientId)
            )
            ->where('number', $number)
            ->exists();
    }

    /**
     * Номер привязан к ученику представителя.
     */
    private static function representativeClientOwnsNumber(int $representativeId, string $number): bool
    {
        return Phone::query()
            ->where('entity_type', Client::class)
            ->whereIn(
                'entity_id',
                Representative::query()
                    ->select('client_id')
                    ->where('id', $representativeId)
            )
            ->where('number', $number)
            ->exists();
    }

}
