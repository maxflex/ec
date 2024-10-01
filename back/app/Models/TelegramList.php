<?php

namespace App\Models;

use App\Casts\JsonArrayCast;
use App\Enums\SendTo;
use App\Http\Resources\PersonResource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TelegramList extends Model
{
    protected $fillable = [
        'recipients', 'send_to', 'scheduled_at', 'text', 'event_id',
        'is_confirmable',
    ];

    protected $casts = [
        'recipients' => JsonArrayCast::class,
        'send_to' => SendTo::class,
        'scheduled_at' => 'datetime',
        'is_confirmable' => 'boolean',
        'is_sent' => 'boolean',
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
        $result = [];

        foreach ($this->recipients as $key => $ids) {
            $people = $key === 'clients'
                ? Client::whereIn('id', $ids)->get()
                : Teacher::whereIn('id', $ids)->get();
            foreach ($people as $person) {
                $phoneIds = collect();
                if ($key === 'clients') {
                    if ($this->send_to === SendTo::students || $this->send_to === SendTo::studentsAndParents) {
                        $phoneIds = $phoneIds->merge(
                            $person->phones()->withTelegram()->pluck('id')
                        );
                    }
                    if ($this->send_to === SendTo::parents || $this->send_to === SendTo::studentsAndParents) {
                        $phoneIds = $phoneIds->merge(
                            $person->parent->phones()->withTelegram()->pluck('id')
                        );
                    }
                } else {
                    $phoneIds = $person->phones()->withTelegram()->pluck('id');
                }
                $result["$key-{$person->id}"] = $this->telegramMessages()
                    ->whereIn('phone_id', $phoneIds)
                    ->join('phones as p', 'p.id', '=', 'phone_id')
                    ->pluck('number');
            }
        }

        return $result;
    }

//    public function sendTelegram(Phone $phone)
//    {
//        $message = Telegram::sendMessage(
//            $phone->telegram_id,
//            $this->text,
//            'HTML',
//        );
//        $this->telegramMessages()->create([
//            'id' => $message->getMessageId(),
//            'text' => $this->text,
//            'phone_id' => $phone->id,
//        ]);
//    }
}
