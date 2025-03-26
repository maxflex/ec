<?php

namespace App\Models;

use App\Casts\JsonArrayCast;
use App\Enums\SendTo;
use App\Enums\TelegramListStatus;
use App\Events\TelegramListSentEvent;
use App\Http\Resources\PersonResource;
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
        'send_to' => SendTo::class,
        'status' => TelegramListStatus::class,
        'scheduled_at' => 'datetime',
        'is_confirmable' => 'boolean',
    ];

    public static function getPeople($recipients)
    {
        $result = [
            'clients' => [],
            'teachers' => [],
        ];

        foreach ($result as $key => $val) {
            $class = $key === 'clients' ? Client::class : Teacher::class;
            $result[$key] = PersonResource::collection(
                $class::whereIn('id', $recipients->{$key})->get()
            );
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
        $result = [
            'clients' => [],
            'parents' => [],
            'teachers' => [],
        ];

        foreach ($this->telegramMessages->groupBy('entity_type') as $entityType => $entityTypeMessages) {
            $key = get_entity_type_key($entityType);
            foreach ($entityTypeMessages->groupBy('entity_id') as $entityId => $telegramMessages) {
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

    private function getScheduledResult()
    {
        $result = [
            'clients' => [],
            'parents' => [],
            'teachers' => [],
        ];

        foreach ($this->recipients as $key => $ids) {
            $people = $key === 'clients'
                ? Client::whereIn('id', $ids)->get()
                : Teacher::whereIn('id', $ids)->get();

            foreach ($people as $person) {
                $result[$key][] = [
                    ...(new PersonResource($person))->toArray(new Request),
                    'messages' => extract_fields_array($person->phones, [
                        'telegram_id', 'number',
                    ]),
                ];
                // "родителям" или "ученикам и родителям"
                if ($key === 'clients' && $this->send_to !== SendTo::students) {
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
}
