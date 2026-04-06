<?php

namespace App\Utils\AI;

use App\Enums\CvpStatus;
use App\Models\AiPrompt;
use App\Models\Call;
use App\Models\Client;
use App\Models\Phone;
use App\Models\Representative;
use App\Models\Request;
use App\Models\Teacher;
use Gemini\Data\Blob;
use Gemini\Enums\MimeType;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use ValueError;

class CallTranscriptionService extends GeminiService
{
    /**
     * Шаг 1: ASR-процесс (audio -> transcript, plain text).
     *
     * @return array{
     *     transcript: string,
     *     instruction: array{
     *         transcription: string|null,
     *         analysis: string|null
     *     }
     * }
     */
    public static function transcribeAudio(Call $call): array
    {
        if (! $call->has_recording) {
            throw new RuntimeException("Нельзя транскрибировать звонок {$call->id}: нет recording_id");
        }

        [$systemInstructionText, $userPromptText] = self::renderCallTranscriptionPrompt($call);
        $audioBytes = self::downloadRecording($call);

        // На первом шаге intentionally без JSON-схемы: ожидаем plain text транскрипта.
        $response = self::buildModel($systemInstructionText)
            ->generateContent([
                $userPromptText,
                new Blob(
                    mimeType: MimeType::AUDIO_MP3,
                    data: base64_encode($audioBytes),
                ),
            ]);

        $transcript = self::extractResponseText($response);
        if (trim($transcript) === '') {
            throw new RuntimeException("Gemini вернул пустой транскрипт для звонка {$call->id}");
        }

        return [
            'transcript' => trim($transcript),
            // Фиксируем фактические instruction/prompt после Blade-рендера (как в отчетах).
            'instruction' => self::buildMergedInstruction(
                $call,
                'transcription',
                self::buildInstructionSnapshot($systemInstructionText, $userPromptText)
            ),
        ];
    }

    /**
     * Рендер prompt-пары для шага 1 (только ASR/транскрипт).
     *
     * @return array{0: string, 1: string}
     */
    private static function renderCallTranscriptionPrompt(Call $call): array
    {
        $phones = self::buildPhonesPromptData($call->number);

        return app(AiPromptRenderer::class)->renderInstructionAndPromptById(
            AiPrompt::CALL_TRANSCRIPTION, [
                'call' => $call,
                'phones' => $phones,
            ]);
    }

    /**
     * Срез phones по номеру для prompt шага анализа.
     * Возвращаем коллекцию моделей с preload entity + готовым item,
     * чтобы в Blade можно было проверять тип напрямую через instanceof.
     *
     * @return Collection<int, Phone>
     */
    private static function buildPhonesPromptData(string $number): Collection
    {
        $phones = Phone::query()
            ->whereNumber($number)
            ->with('entity')
            ->latest('id')
            ->get();

        return $phones
            ->map(function (Phone $phone): Phone {
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
            })
            ->values();
    }

    private static function buildClientItem(Client $client): object
    {
        // Для анализа важно сразу отдать направления и текущий статус обучения.
        $client->loadMissing('directions');

        $directions = $client->directions
            ->sortBy('year')
            ->map(fn ($direction) => (object) [
                // Дублируем фронтовый формат из Client/Directions.vue: 25-26, 26-27 и т.д.
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
            // Для prompt отдаем только год/направление (без технических статусов).
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
        // $startYear = $year - 2000;
        $startYear = $year;
        $endYear = $startYear + 1;

        return "{$startYear}-{$endYear} учебный год";
    }

    /**
     * Скачивает запись Mango по прямой ссылке и возвращает бинарные данные.
     */
    private static function downloadRecording(Call $call): string
    {
        $recording = $call->getRecording('download');

        $response = Http::withOptions([
            'proxy' => '37.140.195.195:8888',
            'verify' => false,
            'timeout' => 180,
        ])->get($recording);

        if (! $response->successful()) {
            throw new RuntimeException("Не удалось скачать запись звонка {$call->id} (HTTP {$response->status()})");
        }

        $body = $response->body();
        if ($body === '') {
            throw new RuntimeException("Получен пустой аудиофайл для звонка {$call->id}");
        }

        return $body;
    }

    /**
     * Безопасно извлекает текст из ответа Gemini, включая multi-part случаи.
     */
    private static function extractResponseText(object $response): string
    {
        try {
            return $response->text();
        } catch (ValueError) {
            $lastPartText = collect($response->parts())->last()?->text;

            return is_string($lastPartText) ? $lastPartText : '';
        }
    }

    /**
     * @param  'transcription'|'analysis'  $key
     * @return array{transcription: string|null, analysis: string|null}
     */
    private static function buildMergedInstruction(Call $call, string $key, string $snapshot): array
    {
        $currentInstruction = is_array($call->instruction) ? $call->instruction : [];

        // Ключи фиксированные, чтобы формат JSON был предсказуемым.
        $normalizedInstruction = [
            'transcription' => isset($currentInstruction['transcription']) && is_string($currentInstruction['transcription'])
                ? $currentInstruction['transcription']
                : null,
            'analysis' => isset($currentInstruction['analysis']) && is_string($currentInstruction['analysis'])
                ? $currentInstruction['analysis']
                : null,
        ];

        $normalizedInstruction[$key] = $snapshot;

        return $normalizedInstruction;
    }

    private static function buildInstructionSnapshot(string $systemInstructionText, string $userPromptText): string
    {
        return trim($systemInstructionText)."\n\n<USER_PROMPT>\n\n".trim($userPromptText);
    }
}
