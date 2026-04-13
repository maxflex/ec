<?php

namespace App\Utils\AI;

use App\Enums\CvpStatus;
use App\Models\Client;
use App\Models\Phone;
use App\Models\Representative;
use App\Models\Request;
use App\Models\Teacher;
use Illuminate\Support\Collection;

class CallPromptPhonesBuilder
{
    /**
     * Подготовка и группировка телефонов для AI-шаблона звонков.
     * Возвращаем только grouped-коллекцию по entity_type.
     *
     * @return Collection<string, Collection<int, Phone>>
     */
    public static function build(string $number): Collection
    {
        return Phone::query()
            // Белый список поддерживаемых сущностей для prompt-контекста.
            ->whereIn('entity_type', [
                Request::class,
                Client::class,
                Representative::class,
                Teacher::class,
            ])
            ->whereNumber($number)
            ->with('entity')
            ->latest('id')
            ->get()
            ->map(fn (Phone $phone): Phone => self::enrichPhoneForPrompt($phone))
            ->values()
            ->groupBy('entity_type');
    }

    private static function enrichPhoneForPrompt(Phone $phone): Phone
    {
        $entity = $phone->entity;

        // Подготавливаем дополнительные поля для prompt-шаблона.
        $phone->setAttribute('item', (object) []);

        if ($entity instanceof Request) {
            $phone->setAttribute('item', (object) [
                'created_at' => $entity->created_at?->format('Y-m-d H:i:s'),
            ]);

            return $phone;
        }

        if ($entity instanceof Client) {
            $phone->setAttribute('item', self::buildClientItem($entity));

            return $phone;
        }

        if ($entity instanceof Representative) {
            $phone->setAttribute('item', (object) [
                'client' => $entity->client
                    ? self::buildClientItem($entity->client)
                    : null,
            ]);

            return $phone;
        }

        if ($entity instanceof Teacher) {
            $phone->setAttribute('item', (object) [
                // Отдаем enum для единообразного чтения в шаблоне: $item->status->getLabel().
                'status' => $entity->status,
            ]);

            return $phone;
        }

        return $phone;
    }

    private static function buildClientItem(Client $client): object
    {
        // Для анализа важно сразу отдать направления и текущий статус обучения.
        $client->loadMissing('directions');

        $directions = $client->directions
            ->sortBy('year')
            ->map(fn ($direction) => (object) [
                // Дублируем фронтовый формат из Client/Directions.vue.
                'year_label' => self::formatDirectionYear((int) $direction->year),
                'status' => $direction->status->value,
                'direction_label' => $direction->direction?->getName(),
                'is_active' => $direction->status !== CvpStatus::finished,
            ])
            ->values()
            ->all();

        return (object) [
            'is_studying_now' => collect($directions)
                ->contains(fn (object $direction): bool => $direction->status !== CvpStatus::finished->value),
            // Для prompt отдаем только человекочитаемые данные без технических статусов.
            'directions' => collect($directions)
                ->map(fn (object $direction): object => (object) [
                    'year_label' => $direction->year_label,
                    'direction_label' => $direction->direction_label,
                    'is_active' => $direction->is_active,
                ])
                ->values()
                ->all(),
        ];
    }

    private static function formatDirectionYear(int $year): string
    {
        $startYear = $year;
        $endYear = $startYear + 1;

        return "{$startYear}-{$endYear} учебный год";
    }
}
