<?php

namespace App\Models;

use App\Enums\EventParticipantConfirmation;
use App\Enums\TeacherStatus;
use App\Facades\Telegram;
use App\Utils\Phone as UtilsPhone;
use App\Utils\Session;
use Exception;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use TelegramBot\Api\Types\ReplyKeyboardRemove;

class Phone extends Model implements Authenticatable
{
    public $timestamps = false;
    protected $fillable = ['number', 'comment'];

    // public function getNumberAttribute($value)
    // {
    //     return $value ? UtilsPhone::format($value) : null;
    // }

    public function getAuthIdentifier()
    {
        return $this->entity_id;
    }

    // @phpstan-ignore return.missing
    public function getAuthIdentifierName()
    {
    }

    // @phpstan-ignore return.missing
    public function getAuthPassword()
    {
    }

    // @phpstan-ignore return.missing
    public function getRememberToken()
    {
    }

    public function setRememberToken($value)
    {
    }

    // @phpstan-ignore return.missing
    public function getRememberTokenName()
    {
    }

    public function entity(): MorphTo
    {
        return $this->morphTo();
    }

    public function telegramMessages(): HasMany
    {
        return $this->hasMany(TelegramMessage::class);
    }

    public function scopeWhereNumber($query, $number)
    {
        return $query->where('number', UtilsPhone::clean($number));
    }

    public function scopeWithTelegram($query)
    {
        return $query->whereNotNull('telegram_id');
    }

    public static function auth($number): ?Phone
    {
        $phones = Phone::query()
            ->whereIn('entity_type', [
                User::class,
                Client::class,
                ClientParent::class,
                Teacher::class,
            ])
            ->whereNumber($number)
            ->get();

        if ($phones->count() !== 1) {
            return null;
        }

        // кандидаты к логину
        $candidates = [];
        foreach ($phones as $phone) {
            $entity = $phone->entity;
            switch ($phone->entity_type) {
                case User::class:
                    if ($entity->is_active) {
                        $candidates[] = $phone;
                    }
                    break;

                case Teacher::class:
                    if ($entity->status === TeacherStatus::active) {
                        $candidates[] = $phone;
                    }
                    break;

                // намеренно нет break
                case ClientParent::class:
                    $entity = $entity->client;

                case Client::class:
                    if ($entity->contracts()->where('year', current_academic_year())->exists()) {
                        $candidates[] = $phone;
                    }
            }

            if (count($candidates) !== 1) {
                return null;
            }

            return $candidates[0];
        }

        return $phones->first();
    }

    public function isAdmin(): bool
    {
        return $this->entity_type === User::class;
    }

    public function isTeacher(): bool
    {
        return $this->entity_type === Teacher::class;
    }

    public function isClient(): bool
    {
        return in_array($this->entity_type, [
            Client::class,
            ClientParent::class
        ]);
    }

    public function sendTelegram(TelegramList $list)
    {
        if ($this->telegram_id === null) {
            throw new Exception('no telegram for phone id: ' . $this->id);
        }

        if ($list->event_id && $list->is_confirmable) {
            $replyMarkup = new InlineKeyboardMarkup([
                [[
                    'text' => '✅ подтвердить участие',
                    'callback_data' => json_encode([
                        'event_id' => $list->event_id,
                        'phone_id' => $this->id,
                        'confirmation' => EventParticipantConfirmation::confirmed->value,
                    ])
                ]], [[
                    'text' => 'отказаться',
                    'callback_data' => json_encode([
                        'event_id' => $list->event_id,
                        'phone_id' => $this->id,
                        'confirmation' => EventParticipantConfirmation::rejected->value,
                    ])
                ]]]);
        } else {
            $replyMarkup = new ReplyKeyboardRemove();
        }

        $message = Telegram::sendMessage(
            $this->telegram_id,
            $list->text,
            'HTML',
            replyMarkup: $replyMarkup,
        );

        $this->telegramMessages()->create([
            'id' => $message->getMessageId(),
            'text' => $list->text,
            'list_id' => $list->id,
        ]);
    }

    public function createSessionToken(): string
    {
        return Session::createToken($this);
    }

    public static function booted()
    {
        static::saving(fn ($phone) => $phone->number = UtilsPhone::clean($phone->number));
    }
}
