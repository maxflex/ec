<?php

namespace App\Utils;

use App\Models\Client;
use App\Models\Event;
use App\Models\EventParticipant;
use App\Models\Teacher;
use Illuminate\Support\Collection;

readonly class PeopleSelector
{
    /**
     * Участники из события + все клиенты и учителя, которых можно выбрать для групповой отправки
     *
     * @return Collection<int, Client|Teacher>
     */
    public static function getForEvent(Event $event): Collection
    {
        $allSelectable = self::getAll();

        $participants = $event->participants()->with('entity')->get();

        // Загрузить отношения в зависимости от типа
        $participants->loadMorph('entity', [
            Client::class => ['directions'], // только для клиента
            Teacher::class => [],             // ничего не грузим
        ]);

        foreach ($participants as $participant) {
            /** @var EventParticipant $participant */
            $entity = $participant->entity;

            // ищем существующую модель в коллекции
            $existing = $allSelectable->first(
                fn ($selectable) => $entity->is($selectable)
            );

            // если нашлась — используем её, иначе — используем исходную entity
            $model = $existing ?? $entity;

            // один раз выставляем атрибут
            $model->setAttribute(
                'event_participant',
                extract_fields($participant, ['confirmation'])
            );

            // если в коллекции не было — добавим
            if (! $existing) {
                $allSelectable->push($model);
            }
        }

        return $allSelectable;
    }

    /**
     * Все клиенты + учителя, которых можно выбрать для групповой отправки
     *
     * @return Collection<int, Client|Teacher>
     */
    public static function getAll(): Collection
    {
        $clients = Client::canLogin()
            // важно: мы всегда говорим "только canLogin", но по факту + текущий учебный год
            ->whereHas('contracts', fn ($q) => $q->where('year', current_academic_year()))
            ->with('directions')
            ->orderByRaw('last_name, first_name, middle_name')
            ->get();

        $teachers = Teacher::canLogin()
            ->orderByRaw('last_name, first_name, middle_name')
            ->get();

        return $clients->concat($teachers);
    }

    /**
     * Распаковать массив типа. Используется на /group-message/send
     *
     * "8663-App\\Models\\Client"
     * "9685-App\\Models\\Client"
     * "9034-App\\Models\\Client"
     *
     * @param  array<int, string>  $selected
     * @return Collection<int, Client|Teacher>
     */
    public static function unpackLocalStorage(array $selected): Collection
    {
        $clientIds = [];
        $teacherIds = [];

        foreach ($selected as $key) {
            [$entityId, $entityType] = explode('-', $key);
            if ($entityType === Client::class) {
                $clientIds[] = $entityId;
            } else {
                $teacherIds[] = $entityId;
            }
        }

        $clients = Client::query()
            ->with('directions', 'phones', 'representative.phones')
            ->whereIn('id', $clientIds)
            ->get();

        $teachers = Teacher::query()
            ->with('phones')
            ->whereIn('id', $teacherIds)
            ->get();

        return $clients->concat($teachers);
    }

    /**
     * Распаковать людей из события. Используется на /group-message/send
     *
     * @return Collection<int, Client|Teacher>
     */
    public static function unpackEvent(Event $event): Collection
    {
        $participants = $event->participants()->with('entity')->get();

        $participants->loadMorph('entity', [
            Client::class => ['directions', 'phones', 'representative.phones'],
            Teacher::class => ['phones'],
        ]);

        return $participants->map(fn (EventParticipant $p) => $p->entity);
    }
}
