<?php

namespace App\Utils;

use App\Enums\Program;
use App\Enums\TelegramTemplate;
use App\Events\ClientReviewMessageEvent;
use App\Facades\Telegram;
use App\Http\Resources\ClientReviewListResource;
use App\Models\Client;
use App\Models\ClientReview;
use App\Models\Teacher;
use App\Models\TelegramMessage;
use Illuminate\Support\Facades\Redis;
use TelegramBot\Api\Types\Message;

class ClientReviewMessage
{
    private Client $client;

    private Teacher $teacher;

    private Program $program;

    private string $cacheKey;

    private array $dimension;

    public function __construct(
        private readonly string $fakeId
    ) {
        [$clientId, $teacherId, $program] = explode('-', $this->fakeId);
        $this->client = Client::findOrFail($clientId);
        $this->teacher = Teacher::findOrFail($teacherId);
        $this->program = Program::tryFrom($program);
        $this->cacheKey = self::getCacheKey($this->client);
        $this->dimension = [
            'client_id' => $this->client->id,
            'teacher_id' => $this->teacher->id,
            'program' => $this->program,
        ];
    }

    private static function getCacheKey(Client $client)
    {
        return 'client-review-message-'.$client->id;
    }

    /**
     * Установка текста отзыва
     * id отзыва к установке текста должен храниться в кэше
     */
    public static function setText(Client $client, Message $message): void
    {
        $clientReviewId = cache()->pull(self::getCacheKey($client));
        if (! $clientReviewId) {
            return;
        }

        $clientReview = ClientReview::findOrFail($clientReviewId);
        $clientReview->text = $message->getText();
        $clientReview->save();

        Telegram::sendMessage(
            $message->getChat()->getId(),
            'Спасибо за отзыв! Нам важно ваше мнение.'
        );

        event(new ClientReviewMessageEvent(
            new ClientReviewListResource($clientReview),
        ));
    }

    public static function getTtl(Client $client, int|string $id): int
    {
        $cacheKey = self::getCacheKey($client);

        if (cache($cacheKey) != $id) {
            return -1;
        }

        return Redis::connection('cache')->ttl(config('cache.prefix').$cacheKey);
    }

    /**
     * Клиент нажал на оценку 1 – 5
     */
    public function setRating(int $rating): void
    {
        $clientReview = ClientReview::create([
            ...$this->dimension,
            'rating' => $rating,
        ]);

        $this->setCache($clientReview->id);
        $this->sendTextMessage($rating);
        event(new ClientReviewMessageEvent(
            new ClientReviewListResource($clientReview),
            $this->fakeId
        ));
    }

    private function setCache(int|string $id): void
    {
        cache([$this->cacheKey => $id], now()->addHours(3));
    }

    /**
     * Второе сообщение: если ученик поставил оценку 1 – 5, ему предлагается
     * дополнить текстом отзыва
     */
    private function sendTextMessage(int $rating): void
    {
        TelegramMessage::sendTemplate(
            TelegramTemplate::clientReviewText,
            $this->client, [
                'rating' => $rating,
            ], [
                'id' => $this->fakeId,
            ],
            $this->fakeId,
        );
    }

    /**
     * Первое сообщение: оставить оценку 1 - 5
     */
    public function sendRatingMessage(): bool
    {
        // уже отправляли, таймаут в 3 часа еще не вышел
        if ($this->cacheKeyExists()) {
            return false;
        }

        $lessonsCountAndYears = ClientReview::getLessonsCountAndYears(
            $this->client->id,
            $this->teacher->id,
            $this->program
        );

        $sent = TelegramMessage::sendTemplate(
            TelegramTemplate::clientReviewRating,
            $this->client, [
                'client' => $this->client,
                'teacher' => $this->teacher,
                'program' => $this->program,
                'lessonsCount' => $lessonsCountAndYears['lessons_count'],
            ], [
                'id' => $this->fakeId,
            ],
            $this->fakeId,
        );

        if ($sent) {
            $this->setCache($this->fakeId);
            event(new ClientReviewMessageEvent(
                new ClientReviewListResource(
                    ClientReview::requirements()->where($this->dimension)->first()
                ),
            ));
        }

        return $sent;
    }

    private function cacheKeyExists(): bool
    {
        return cache()->has($this->cacheKey);
    }

    // private function removeCacheKey(): void
    // {
    //     cache()->forget($this->cacheKey);
    // }
}
