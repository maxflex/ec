<?php

namespace App\Models;

use App\Enums\EventParticipantConfirmation;
use Illuminate\Database\Eloquent\Model;
use TelegramBot\Api\Types\CallbackQuery;

class EventParticipant extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'entity_type', 'entity_id', 'confirmation',
    ];

    protected $casts = [
        'confirmation' => EventParticipantConfirmation::class,
    ];

    public static function confirm($data, \TelegramBot\Api\Client $bot, CallbackQuery $callback)
    {
        $confirmation = EventParticipantConfirmation::from($data->confirmation);
        $message = $callback->getMessage();

        $answerText = $confirmation === EventParticipantConfirmation::confirmed
            ? '✅ Вы подтвердили участие'
            : '❌ Вы отказались от участия';

        $bot->answerCallbackQuery(
            $callback->getId(),
            $answerText,
        );

        $bot->editMessageText(
            $message->getChat()->getId(),
            $message->getMessageId(),
            $message->getText().PHP_EOL.PHP_EOL."<b>{$answerText}</b>",
            'HTML'
        );

        $phone = Phone::find($data->phone_id);
        $query = EventParticipant::where('event_id', $data->event_id);

        if ($phone->entity_type === ClientParent::class) {
            $query->where([
                ['entity_type', Client::class],
                ['entity_id', $phone->entity->client->id],
            ]);
        } else {
            $query->where([
                ['entity_type', $phone->entity_type],
                ['entity_id', $phone->entity_id],
            ]);
        }

        $participant = $query->first();
        $participant?->update([
            'confirmation' => $confirmation,
        ]);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function entity()
    {
        return $this->morphTo('entity');
    }
}
