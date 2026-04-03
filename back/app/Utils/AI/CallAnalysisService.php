<?php

namespace App\Utils\AI;

use App\Enums\CallerType;
use App\Enums\CvpStatus;
use App\Models\AiPrompt;
use App\Models\Call;
use App\Models\Client;
use App\Models\Phone;
use App\Models\Representative;
use App\Models\Request;
use App\Models\Teacher;
use Gemini\Data\GenerationConfig;
use Gemini\Data\Schema;
use Gemini\Enums\DataType;
use Gemini\Enums\ResponseMimeType;
use Illuminate\Support\Collection;
use RuntimeException;
use ValueError;

class CallAnalysisService extends GeminiService
{
    /**
     * Минимальная длительность разговора для запуска AI-анализа.
     */
    public const int MIN_DURATION_SECONDS = 10;

    /**
     * Можно ли запускать AI-анализ для звонка.
     */
    public static function shouldAnalyze(Call $call): bool
    {
        return $call->duration > self::MIN_DURATION_SECONDS;
    }

    /**
     * Шаг 2: transcript -> summary + analysis + caller_type.
     *
     * @return array{
     *     summary: string,
     *     analysis: string|null, // пустой анализ сохраняется как null
     *     caller_type: string,
     *     instruction: array{
     *         transcription: string|null,
     *         analysis: string|null
     *     }
     * }
     */
    public static function analyzeTranscript(Call $call): array
    {
        // Анализ опирается строго на транскрипт, сохраненный в модели звонка.
        $transcript = trim((string) $call->transcript);
        if ($transcript === '') {
            throw new RuntimeException("Нельзя анализировать пустой транскрипт для звонка {$call->id}");
        }

        [$systemInstructionText, $userPromptText] = self::renderCallAnalysisPrompt($call);

        // На втором шаге запрашиваем сводку, анализ и тип собеседника.
        $schema = new Schema(
            type: DataType::OBJECT,
            properties: [
                'summary' => new Schema(
                    type: DataType::STRING,
                    description: 'КРАТКОЕ СОДЕРЖАНИЕ РАЗГОВОРА'
                ),
                'analysis' => new Schema(
                    type: DataType::STRING,
                    description: 'АНАЛИЗ ЗВОНКА'
                ),
                'caller_type' => new Schema(
                    type: DataType::STRING,
                    description: 'ТИП РАЗГОВОРА'
                ),
            ],
            required: ['summary', 'analysis', 'caller_type']
        );

        $response = self::buildModel($systemInstructionText)
            ->withGenerationConfig(
                new GenerationConfig(
                    responseMimeType: ResponseMimeType::APPLICATION_JSON,
                    responseSchema: $schema
                )
            )
            ->generateContent([
                $userPromptText,
            ]);

        $result = $response->json(true);

        // Для summary и caller_type поле обязательно и не может быть пустым.
        foreach (['summary', 'caller_type'] as $field) {
            if (! isset($result[$field]) || ! is_string($result[$field]) || trim($result[$field]) === '') {
                throw new RuntimeException("Gemini вернул пустое поле '{$field}' для звонка {$call->id}");
            }
        }

        try {
            $callerType = CallerType::from(trim($result['caller_type']));
        } catch (ValueError) {
            throw new RuntimeException(
                "Gemini вернул недопустимый caller_type '{$result['caller_type']}' для звонка {$call->id}"
            );
        }

        // Пустой analysis допустим: нормализуем его к null для единообразного хранения.
        $analysis = isset($result['analysis']) && is_string($result['analysis'])
            ? trim($result['analysis'])
            : null;

        if ($analysis === '') {
            $analysis = null;
        }

        return [
            'summary' => trim($result['summary']),
            'analysis' => $analysis,
            'caller_type' => $callerType->value,
            // Фиксируем фактические instruction/prompt после Blade-рендера (как в отчетах).
            'instruction' => self::buildMergedInstruction(
                $call,
                'analysis',
                self::buildInstructionSnapshot($systemInstructionText, $userPromptText)
            ),
        ];
    }

    /**
     * Рендер prompt-пары для шага 2 (анализ по готовому транскрипту).
     * Важно: prompt должен использовать {{ $call->transcript }}.
     *
     * @return array{0: string, 1: string}
     */
    private static function renderCallAnalysisPrompt(Call $call): array
    {
        // В шаблон передаем готовый срез phones по номеру звонка.
        // Внутри каждой записи уже есть человекочитаемый entity_type и расширенный item.
        $phones = self::buildPhonesPromptData($call->number);

        return app(AiPromptRenderer::class)->renderInstructionAndPromptById(
            AiPrompt::CALL_ANALYSIS, [
                'call' => $call,
                'aon' => Call::aon($call->number),
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
