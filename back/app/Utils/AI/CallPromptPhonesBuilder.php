<?php

namespace App\Utils\AI;

use App\Enums\CvpStatus;
use App\Models\Client;
use App\Models\Call;
use App\Models\Pass;
use App\Models\Phone;
use App\Models\Representative;
use App\Models\Request;
use App\Models\Teacher;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;

class CallPromptPhonesBuilder
{
    /**
     * Подготовка и группировка телефонов для AI-шаблона звонков.
     * Дополнительно отдаем использованные пропуска за последние 120 дней
     * относительно времени звонка.
     *
     * @return array{
     *     phones: Collection<string, Collection<int, Phone>>,
     *     passes: Collection<int, Pass>
     * }
     */
    public static function build(Call $call): array
    {
        $phones = Phone::query()
            // Белый список поддерживаемых сущностей для prompt-контекста.
            ->whereIn('entity_type', [
                Request::class,
                Client::class,
                Representative::class,
                Teacher::class,
            ])
            ->whereNumber($call->number)
            ->with('entity')
            ->latest('id')
            ->get()
            ->map(fn (Phone $phone): Phone => self::enrichPhoneForPrompt($phone))
            ->values()
            ->groupBy('entity_type');

        $passes = self::buildPasses($phones, $call);

        return [
            'phones' => $phones,
            'passes' => $passes,
        ];
    }

    /**
     * Возвращаем только использованные пропуска в окне [T-120 дней, T],
     * где T — время звонка.
     *
     * @param  Collection<string, Collection<int, Phone>>  $phones
     * @return Collection<int, Pass>
     */
    private static function buildPasses(Collection $phones, Call $call): Collection
    {
        /** @var Collection<int, Phone> $requestPhones */
        $requestPhones = $phones[Request::class] ?? collect();
        if ($requestPhones->isEmpty()) {
            return collect();
        }

        $requests = new EloquentCollection(
            $requestPhones
                ->map(fn (Phone $phone): ?Request => $phone->entity instanceof Request ? $phone->entity : null)
                ->filter()
                ->unique('id')
                ->values()
                ->all()
        );

        if ($requests->isEmpty()) {
            return collect();
        }

        $to = $call->created_at
            ? CarbonImmutable::parse($call->created_at)
            : CarbonImmutable::now();
        $from = $to->subDays(120);

        $requests->load([
            'passes' => fn ($query) => $query
                ->whereHas('passLog', fn ($q) => $q->whereBetween('used_at', [
                    $from->format('Y-m-d H:i:s'),
                    $to->format('Y-m-d H:i:s'),
                ]))
                ->with(['passLog', 'request'])
                ->latest('id'),
        ]);

        return $requests
            ->flatMap(fn (Request $request): EloquentCollection => $request->passes)
            ->sortByDesc(fn (Pass $pass): string => (string) $pass->used_at)
            ->values();
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
