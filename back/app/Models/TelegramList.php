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

class TelegramList extends Model
{
    protected $fillable = [
        'recipients', 'send_to', 'scheduled_at', 'text',
        'event_id', 'is_confirmable', 'status'
    ];

    protected $casts = [
        'recipients' => JsonArrayCast::class,
        'send_to' => SendTo::class,
        'status' => TelegramListStatus::class,
        'scheduled_at' => 'datetime',
        'is_confirmable' => 'boolean',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function telegramMessages(): HasMany
    {
        return $this->hasMany(TelegramMessage::class, 'list_id');
    }

    public static function getPeople($recipients)
    {
        $result = [
            'clients' => [],
            'teachers' => []
        ];

        foreach ($result as $key => $val) {
            $class = $key === 'clients' ? Client::class : Teacher::class;
            $result[$key] = PersonResource::collection(
                $class::whereIn('id', $recipients->{$key})->get()
            );
        }

        return $result;
    }


    public function getResults()
    {
        return $this->status === TelegramListStatus::sent
            ? $this->getSentResults()
            : $this->getScheduledResults();
    }

    private function getSentResults()
    {
        $result = [];
        foreach ($this->telegramMessages as $telegramMessage) {
            if ($telegramMessage->entity_type === ClientParent::class) {
                $type = 'clients';
                $id = $telegramMessage->entity->client->id;
            } else {
                $type = $telegramMessage->entity_type === Teacher::class ? 'teachers' : 'clients';
                $id = $telegramMessage->entity_id;
            }

            $key = "$type-$id";
            if (!isset($result[$key])) {
                $result[$key] = [];
            }
            $result[$key][] = [
                'id' => $telegramMessage->id,
                'is_sent' => $telegramMessage->telegram_id !== null,
                'is_parent' => $telegramMessage->entity_type === ClientParent::class,
                'number' => $telegramMessage->number,
            ];
        }
        return $result;
    }

    private function getScheduledResults()
    {
        $result = [];
        foreach ($this->recipients as $type => $ids) {
            $people = $type === 'clients'
                ? Client::whereIn('id', $ids)->get()
                : Teacher::whereIn('id', $ids)->get();
            foreach ($people as $person) {
                $key = "$type-$person->id";
                $result[] = [];
                $phones = $person->phones()->get();
                if ($type === 'clients') {
                    $phones = $phones->merge(
                        $person->parent->phones()->get()
                    );
                }
                foreach ($phones as $phone) {
                    if ($phone->entity_type === ClientParent::class) {
                        $key = "clients-{$phone->entity->client->id}";
                    }
                    $result[$key][] = [
                        'id' => $phone->id,
                        'is_sent' => $phone->telegram_id !== null,
                        'is_parent' => $phone->entity_type === ClientParent::class,
                        'number' => $phone->number,
                    ];
                }
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
}
