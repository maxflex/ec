<?php

namespace App\Models;

use App\Casts\JsonArrayCast;
use App\Casts\Set;
use App\Enums\SendTo;
use App\Enums\TelegramListStatus;
use App\Events\TelegramListSentEvent;
use App\Http\Resources\PersonResource;
use App\Http\Resources\PersonWithPhonesResource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;

class TelegramList extends Model
{
    protected $fillable = [
        'recipients', 'send_to', 'scheduled_at', 'text',
        'event_id', 'is_confirmable', 'status',
    ];

    protected $casts = [
        'recipients' => JsonArrayCast::class,
        'send_to' => Set::class,
        'status' => TelegramListStatus::class,
        'scheduled_at' => 'datetime',
        'is_confirmable' => 'boolean',
    ];

    public static function getPeople($recipients)
    {
        $result = [];

        foreach (['clients', 'teachers'] as $key) {
            switch ($key) {
                case 'clients':
                    $clients = Client::with('parent')->whereIn('id', $recipients->clients)->get();
                    $result['students'] = PersonWithPhonesResource::collection($clients);
                    $result['parents'] = PersonWithPhonesResource::collection($clients->map(fn ($c) => $c->parent));
                    break;

                case 'teachers':
                    $teachers = Teacher::whereIn('id', $recipients->teachers)->get();
                    $result['teachers'] = PersonWithPhonesResource::collection($teachers);
                    break;
            }
        }

        return $result;
    }

    public static function booted()
    {
        static::updated(function (TelegramList $telegramList) {
            if ($telegramList->isDirty('status')) {
                event(new TelegramListSentEvent($telegramList));
            }
        });
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function telegramMessages(): HasMany
    {
        return $this->hasMany(TelegramMessage::class, 'list_id');
    }

    public function getResult()
    {
        return $this->status === TelegramListStatus::sent
            ? $this->getSentResult()
            : $this->getScheduledResult();
    }

    private function getSentResult()
    {
        $result = $this->getResultDefaults();

        foreach ($this->telegramMessages->groupBy('entity_type') as $entityType => $entityTypeMessages) {
            $key = get_entity_type_key($entityType);
            foreach ($entityTypeMessages->groupBy('entity_id') as $telegramMessages) {
                $entity = $telegramMessages->first()->entity;
                $result[$key][] = [
                    ...(new PersonResource($entity))->toArray(new Request),
                    'messages' => extract_fields_array($telegramMessages, [
                        'telegram_id', 'number',
                    ]),
                ];
            }
        }

        return $result;
    }

    /**
     * 'students' => [],
     * 'parents' => [],
     * 'teachers' => [],
     */
    private function getResultDefaults(): array
    {
        return collect(SendTo::cases())->mapWithKeys(fn (SendTo $sendTo) => [
            $sendTo->value => [],
        ])->values()->all();
    }

    private function getScheduledResult()
    {
        $result = $this->getResultDefaults();

        foreach ($this->recipients as $recipient => $ids) {
            $class = $recipient === 'clients' ? Client::class : Teacher::class;
            $people = $class::whereIn('id', $ids)->get();
            $key = get_entity_type_key($class);

            foreach ($people as $person) {
                $result[$key][] = [
                    ...(new PersonResource($person))->toArray(new Request),
                    'messages' => extract_fields_array($person->phones, [
                        'telegram_id', 'number',
                    ]),
                ];
                // родителям
                if ($key === 'students' && in_array(SendTo::parents->value, $this->send_to)) {
                    $result['parents'][] = [
                        ...(new PersonResource($person->parent))->toArray(new Request),
                        'messages' => extract_fields_array($person->parent->phones, [
                            'telegram_id', 'number',
                        ]),
                    ];
                }
            }
        }

        return $result;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
