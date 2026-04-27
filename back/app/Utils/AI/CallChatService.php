<?php

namespace App\Utils\AI;

use App\Models\AiPrompt;
use Gemini\Data\GenerationConfig;
use Gemini\Data\Schema;
use Gemini\Enums\DataType;
use Gemini\Enums\ResponseMimeType;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;
use ValueError;

class CallChatService extends GeminiService
{
    private const string INTENT_SQL_SEARCH = 'sql_search';

    private const string INTENT_CONTEXT_ANSWER = 'context_answer';

    private const string INTENT_DIRECT_ANSWER = 'direct_answer';

    private const string FALLBACK_DIRECT_MESSAGE = 'Я могу помочь с вопросами только по звонкам.';

    // Ограничиваем размер выборки для второго LLM-вызова при массовых запросах.
    private const int MAX_ROWS_FOR_FINAL_ANSWER = 200;

    // Верхний бюджет JSON-пакета rows, который отправляем во второй LLM-вызов.
    // Это не "жесткий лимит продукта", а защита от деградации и переполнения контекста.
    private const int MAX_ROWS_PAYLOAD_BYTES = 1_500_000;

    // Для массовых выборок режем длинные тексты, чтобы сохранить максимум строк в контексте.
    private const int MAX_TEXT_LENGTH_FOR_MULTI_ROWS = 12_000;

    private const int MAX_TRANSCRIPT_LENGTH_FOR_MULTI_ROWS = 120_000;

    // Аварийный fallback, если даже после обычной упаковки payload остаётся слишком большим.
    private const int EMERGENCY_TEXT_LENGTH = 2_000;

    private const int EMERGENCY_TRANSCRIPT_LENGTH = 12_000;

    private const int MAX_CONTEXT_MESSAGES = 12;

    private const int MAX_CONTEXT_TEXT_LENGTH = 1500;

    // По умолчанию используем отдельный DB-коннект под read-only пользователя ai_calls.
    private const string DB_CONNECTION_CONFIG_KEY = 'database.ai_calls_connection';

    /**
     * Шаги MVP-чата:
     * 1) router (JSON) -> 2) SQL -> 3) финальный человекочитаемый ответ.
     *
     * @return array{
     *     intent: string,
     *     answer: string,
     *     sql_query: string|null,
     *     rows_count: int
     * }
     */
    public static function reply(string $rawPrompt, array $rawMessages = []): array
    {
        $prompt = self::normalizePrompt($rawPrompt);
        $messages = self::normalizeMessages($rawMessages);

        if ($prompt === '') {
            return [
                'intent' => self::INTENT_DIRECT_ANSWER,
                'answer' => self::FALLBACK_DIRECT_MESSAGE,
                'sql_query' => null,
                'rows_count' => 0,
            ];
        }

        $routerResult = self::routePrompt($prompt, $messages);
        if ($routerResult['intent'] === self::INTENT_CONTEXT_ANSWER) {
            return [
                'intent' => self::INTENT_CONTEXT_ANSWER,
                'answer' => self::buildContextAnswer($prompt, $messages, $routerResult['direct_message']),
                'sql_query' => null,
                'rows_count' => 0,
            ];
        }

        if ($routerResult['intent'] === self::INTENT_DIRECT_ANSWER) {
            // Если есть история, а роутер вернул только "отказ",
            // пробуем ответить в контексте диалога без SQL.
            if ($messages !== [] && self::isFallbackDirectMessage($routerResult['direct_message'])) {
                return [
                    'intent' => self::INTENT_CONTEXT_ANSWER,
                    'answer' => self::buildContextAnswer($prompt, $messages, null),
                    'sql_query' => null,
                    'rows_count' => 0,
                ];
            }

            return [
                'intent' => self::INTENT_DIRECT_ANSWER,
                'answer' => $routerResult['direct_message'],
                'sql_query' => null,
                'rows_count' => 0,
            ];
        }

        $sqlQuery = $routerResult['sql_query'];
        $dbConnection = (string) config(self::DB_CONNECTION_CONFIG_KEY, 'mysql_ai_calls');

        try {
            $rows = DB::connection($dbConnection)->select($sqlQuery);
        } catch (QueryException $e) {
            throw new RuntimeException(
                "Не удалось выполнить SQL, сгенерированный AI: {$e->getMessage()}",
                previous: $e
            );
        }

        $normalizedRows = self::normalizeRows($rows);

        return [
            'intent' => self::INTENT_SQL_SEARCH,
            'answer' => self::buildFinalAnswer($prompt, $sqlQuery, $normalizedRows, $messages),
            'sql_query' => $sqlQuery,
            'rows_count' => count($normalizedRows),
        ];
    }

    /**
     * Поддерживаем формат команды "/calls ...", но в модель передаем только смысловой текст вопроса.
     */
    private static function normalizePrompt(string $rawPrompt): string
    {
        $prompt = trim($rawPrompt);
        if (preg_match('/^\/calls\b/ui', $prompt) === 1) {
            $prompt = trim((string) preg_replace('/^\/calls\b/ui', '', $prompt, 1));
        }

        return $prompt;
    }

    /**
     * Нормализуем историю: чистим HTML, пустые реплики и оставляем последние сообщения.
     *
     * @param  array<int, array<string, string>>  $rawMessages
     * @return array<int, array{role: 'user'|'assistant', text: string}>
     */
    private static function normalizeMessages(array $rawMessages): array
    {
        $normalized = collect($rawMessages)
            ->filter(
                fn ($message): bool => is_array($message)
                    && isset($message['role'], $message['text'])
                    && is_string($message['role'])
                    && is_string($message['text'])
                    && in_array($message['role'], ['user', 'assistant'], true)
            )
            ->map(function (array $message): array {
                $text = trim(strip_tags($message['text']));

                return [
                    'role' => $message['role'] === 'assistant' ? 'assistant' : 'user',
                    'text' => Str::limit($text, self::MAX_CONTEXT_TEXT_LENGTH),
                ];
            })
            ->filter(fn (array $message): bool => $message['text'] !== '')
            ->values();

        if ($normalized->count() > self::MAX_CONTEXT_MESSAGES) {
            $normalized = $normalized->slice(-self::MAX_CONTEXT_MESSAGES)->values();
        }

        return $normalized->all();
    }

    /**
     * @return array{
     *     intent: 'sql_search'|'context_answer'|'direct_answer',
     *     sql_query: string,
     *     direct_message: string
     * }
     */
    private static function routePrompt(string $prompt, array $messages): array
    {
        // Для роутера используем только instruction из ai_prompts.id=7.
        // Поле prompt в этом сценарии зарезервировано под общие правила финального ответа.
        $systemInstructionText = app(AiPromptRenderer::class)->renderByIdWithScope(
            AiPrompt::CALLS_CHAT,
            ['prompt' => $prompt]
        );
        $userPromptText = self::buildRouterUserPrompt($prompt);

        $schema = new Schema(
            type: DataType::OBJECT,
            properties: [
                'intent' => new Schema(
                    type: DataType::STRING,
                    enum: [self::INTENT_SQL_SEARCH, self::INTENT_CONTEXT_ANSWER, self::INTENT_DIRECT_ANSWER]
                ),
                'sql_query' => new Schema(type: DataType::STRING, nullable: true),
                'direct_message' => new Schema(type: DataType::STRING, nullable: true),
            ],
            required: ['intent', 'sql_query', 'direct_message']
        );

        $response = self::buildModel($systemInstructionText)
            ->withGenerationConfig(
                new GenerationConfig(
                    responseMimeType: ResponseMimeType::APPLICATION_JSON,
                    responseSchema: $schema
                )
            )
            ->generateContent([self::buildRouterInput($userPromptText, $messages)]);

        try {
            $result = $response->json(true);
        } catch (ValueError) {
            throw new RuntimeException('Gemini вернул невалидный JSON в router-режиме');
        }

        if (! is_array($result) || ! isset($result['intent']) || ! is_string($result['intent'])) {
            throw new RuntimeException('Gemini не вернул обязательное поле intent');
        }

        if ($result['intent'] === self::INTENT_DIRECT_ANSWER) {
            return [
                'intent' => self::INTENT_DIRECT_ANSWER,
                'sql_query' => '',
                'direct_message' => self::resolveDirectMessage(
                    isset($result['direct_message']) && is_string($result['direct_message'])
                        ? $result['direct_message']
                        : null
                ),
            ];
        }

        if ($result['intent'] === self::INTENT_CONTEXT_ANSWER) {
            return [
                'intent' => self::INTENT_CONTEXT_ANSWER,
                'sql_query' => '',
                'direct_message' => self::resolveContextDraftAnswer(
                    isset($result['direct_message']) && is_string($result['direct_message'])
                        ? $result['direct_message']
                        : null
                ),
            ];
        }

        if ($result['intent'] !== self::INTENT_SQL_SEARCH) {
            throw new RuntimeException("Gemini вернул неизвестный intent '{$result['intent']}'");
        }

        $sqlQuery = isset($result['sql_query']) && is_string($result['sql_query'])
            ? trim($result['sql_query'])
            : '';

        if ($sqlQuery === '') {
            throw new RuntimeException('Gemini вернул пустой sql_query при intent=sql_search');
        }

        return [
            'intent' => self::INTENT_SQL_SEARCH,
            'sql_query' => $sqlQuery,
            'direct_message' => '',
        ];
    }

    private static function buildRouterUserPrompt(string $prompt): string
    {
        return "Вопрос пользователя:\n{$prompt}";
    }

    /**
     * Добавляем историю в первый LLM-вызов, чтобы поддерживать follow-up вопросы.
     *
     * @param  array<int, array{role: 'user'|'assistant', text: string}>  $messages
     */
    private static function buildRouterInput(string $userPromptText, array $messages): string
    {
        if ($messages === []) {
            return $userPromptText;
        }

        return trim($userPromptText)."\n\n<CHAT_HISTORY>\n"
            .self::buildConversationContext($messages)
            ."\n</CHAT_HISTORY>\n\nУчитывай историю и отвечай только на текущий запрос пользователя.";
    }

    /**
     * Приводим историю к простому тексту для промпта.
     *
     * @param  array<int, array{role: 'user'|'assistant', text: string}>  $messages
     */
    private static function buildConversationContext(array $messages): string
    {
        return collect($messages)
            ->map(function (array $message): string {
                $role = $message['role'] === 'assistant' ? 'assistant' : 'user';

                return "{$role}: {$message['text']}";
            })
            ->implode("\n");
    }

    private static function resolveDirectMessage(?string $directMessage): string
    {
        $directMessage = is_string($directMessage) ? trim($directMessage) : '';

        return $directMessage === '' ? self::FALLBACK_DIRECT_MESSAGE : $directMessage;
    }

    private static function resolveContextDraftAnswer(?string $directMessage): string
    {
        return is_string($directMessage) ? trim($directMessage) : '';
    }

    /**
     * Контекстный ответ по истории без SQL.
     *
     * @param  array<int, array{role: 'user'|'assistant', text: string}>  $messages
     */
    private static function buildContextAnswer(string $question, array $messages, ?string $routerDraftAnswer): string
    {
        $sharedOutputRules = self::renderSharedOutputRulesFromPrompt($question);

        $systemInstructionText = <<<'PROMPT'
Ты аналитик звонков.
Отвечай только на основе истории текущего чата.
Если по истории данных недостаточно, прямо попроси уточнение.
Не выдумывай факты и не ссылайся на несуществующие звонки.
PROMPT;
        if ($sharedOutputRules !== '') {
            $systemInstructionText .= "\n\n<OUTPUT_RULES>\n{$sharedOutputRules}\n</OUTPUT_RULES>";
        }

        $payloadData = [
            'question' => $question,
        ];

        if ($messages !== []) {
            $payloadData['chat_history'] = $messages;
        }

        if (is_string($routerDraftAnswer) && trim($routerDraftAnswer) !== '') {
            // Черновик роутера можно использовать как подсказку, но не как обязательный финальный текст.
            $payloadData['router_draft_answer'] = trim($routerDraftAnswer);
        }

        $payload = json_encode($payloadData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if (! is_string($payload)) {
            throw new RuntimeException('Не удалось сериализовать данные для context_answer');
        }

        $response = self::buildModel($systemInstructionText)->generateContent([
            "Сформируй итоговый ответ для пользователя на основе контекста:\n{$payload}",
        ]);

        return self::extractResponseText($response);
    }

    /**
     * Хак: используем ai_prompts.id=7.prompt как единый источник правил оформления финального ответа.
     */
    private static function renderSharedOutputRulesFromPrompt(string $question): string
    {
        $promptTemplate = AiPrompt::query()
            ->whereKey(AiPrompt::CALLS_CHAT)
            ->value('prompt');

        if (! is_string($promptTemplate) || trim($promptTemplate) === '') {
            throw new RuntimeException('ai_prompts.id=7.prompt должен быть заполнен');
        }

        $renderedPrompt = Blade::render($promptTemplate, ['prompt' => $question]);
        $renderedPrompt = trim($renderedPrompt);

        if ($renderedPrompt === '') {
            throw new RuntimeException('ai_prompts.id=7.prompt после Blade-рендера не должен быть пустым');
        }

        return $renderedPrompt;
    }

    /**
     * Единый safe-extract текста из Gemini-ответа (включая multi-part).
     */
    private static function extractResponseText(object $response): string
    {
        try {
            $text = $response->text();
        } catch (ValueError) {
            $text = collect($response->parts())->last()?->text;
        }

        if (! is_string($text) || trim($text) === '') {
            throw new RuntimeException('Gemini вернул пустой финальный ответ');
        }

        return trim($text);
    }

    private static function isFallbackDirectMessage(string $message): bool
    {
        return mb_strtolower(trim($message)) === mb_strtolower(self::FALLBACK_DIRECT_MESSAGE);
    }

    /**
     * Нормализуем SQL-результат:
     * - приводим объекты к массивам;
     * - чистим HTML только в transcript;
     * - не обрезаем текст на этом шаге.
     *
     * @param  array<int, object>  $rows
     * @return array<int, array<string, string|int|float|bool|null>>
     */
    private static function normalizeRows(array $rows): array
    {
        return collect($rows)
            ->map(function (object $row): array {
                return collect((array) $row)
                    ->map(function (string|int|float|bool|null $value, string $field): string|int|float|bool|null {
                        if (! is_string($value)) {
                            return $value;
                        }

                        $text = $field === 'transcript' ? strip_tags($value) : $value;
                        return trim($text);
                    })
                    ->all();
            })
            ->values()
            ->all();
    }

    /**
     * Упаковка rows для финального LLM-вызова.
     *
     * Правила:
     * - если в выборке ровно 1 строка, отдаём её полностью (без обрезки transcript);
     * - если строк много, ограничиваем размер строки и общий payload.
     *
     * @param  array<int, array<string, string|int|float|bool|null>>  $rows
     * @return array<int, array<string, string|int|float|bool|null>>
     */
    private static function prepareRowsForFinalAnswer(array $rows): array
    {
        if ($rows === []) {
            return [];
        }

        if (count($rows) === 1) {
            return $rows;
        }

        $rows = array_slice($rows, 0, self::MAX_ROWS_FOR_FINAL_ANSWER);
        $compressedRows = array_map(fn (array $row): array => self::compressRowForMultiRows($row), $rows);

        if (self::estimateJsonBytes($compressedRows) <= self::MAX_ROWS_PAYLOAD_BYTES) {
            return $compressedRows;
        }

        return self::cropRowsByPayloadBudget($compressedRows, self::MAX_ROWS_PAYLOAD_BYTES);
    }

    /**
     * Для массовых выборок сохраняем больше строк, но с ограничением длины текстовых полей.
     *
     * @param  array<string, string|int|float|bool|null>  $row
     * @return array<string, string|int|float|bool|null>
     */
    private static function compressRowForMultiRows(array $row): array
    {
        return collect($row)
            ->map(function (string|int|float|bool|null $value, string $field): string|int|float|bool|null {
                if (! is_string($value)) {
                    return $value;
                }

                $text = trim($value);

                if ($field === 'transcript') {
                    return Str::limit($text, self::MAX_TRANSCRIPT_LENGTH_FOR_MULTI_ROWS, '');
                }

                return Str::limit($text, self::MAX_TEXT_LENGTH_FOR_MULTI_ROWS, '');
            })
            ->all();
    }

    /**
     * @param  array<int, array<string, string|int|float|bool|null>>  $rows
     */
    private static function estimateJsonBytes(array $rows): int
    {
        $json = json_encode($rows, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return is_string($json) ? strlen($json) : 0;
    }

    /**
     * @param  array<int, array<string, string|int|float|bool|null>>  $rows
     * @return array<int, array<string, string|int|float|bool|null>>
     */
    private static function cropRowsByPayloadBudget(array $rows, int $budgetBytes): array
    {
        $result = [];
        $usedBytes = 0;

        foreach ($rows as $row) {
            $rowBytes = self::estimateJsonBytes([$row]);
            $canAppend = $usedBytes + $rowBytes <= $budgetBytes;

            if ($result !== [] && ! $canAppend) {
                break;
            }

            if ($result === [] && ! $canAppend) {
                // Если первая же строка слишком большая, применяем аварийное сжатие.
                $result[] = self::makeEmergencyCompactRow($row);
                break;
            }

            $result[] = $row;
            $usedBytes += $rowBytes;
        }

        return $result === [] ? [self::makeEmergencyCompactRow($rows[0])] : $result;
    }

    /**
     * @param  array<string, string|int|float|bool|null>  $row
     * @return array<string, string|int|float|bool|null>
     */
    private static function makeEmergencyCompactRow(array $row): array
    {
        return collect($row)
            ->map(function (string|int|float|bool|null $value, string $field): string|int|float|bool|null {
                if (! is_string($value)) {
                    return $value;
                }

                $text = trim($value);

                if ($field === 'transcript') {
                    return Str::limit($text, self::EMERGENCY_TRANSCRIPT_LENGTH, '');
                }

                return Str::limit($text, self::EMERGENCY_TEXT_LENGTH, '');
            })
            ->all();
    }

    /**
     * Второй вызов Gemini: превращаем табличный результат SQL в удобный ответ пользователю.
     *
     * @param  array<int, array<string, string|int|float|bool|null>>  $rows
     * @param  array<int, array{role: 'user'|'assistant', text: string}>  $messages
     */
    private static function buildFinalAnswer(string $question, string $sqlQuery, array $rows, array $messages): string
    {
        $sharedOutputRules = self::renderSharedOutputRulesFromPrompt($question);
        $rowsForModel = self::prepareRowsForFinalAnswer($rows);

        $systemInstructionText = <<<'PROMPT'
Ты аналитик звонков.
Отвечай только на основе переданных SQL-результатов.
Отвечай по-русски, коротко и по делу.
Если данных нет, так и скажи без выдумывания причин.
PROMPT;
        if ($sharedOutputRules !== '') {
            $systemInstructionText .= "\n\n<OUTPUT_RULES>\n{$sharedOutputRules}\n</OUTPUT_RULES>";
        }

        $payloadData = [
            'question' => $question,
            'sql_query' => $sqlQuery,
            // Полный размер SQL-результата до упаковки.
            'rows_count' => count($rows),
            // Реальные строки, которые ушли в модель после упаковки.
            'rows_for_model_count' => count($rowsForModel),
            'rows' => $rowsForModel,
        ];

        // Если истории нет, не передаем про нее ничего во второй LLM-вызов.
        if ($messages !== []) {
            $payloadData['chat_history'] = $messages;
        }

        $payload = json_encode($payloadData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if (! is_string($payload)) {
            throw new RuntimeException('Не удалось сериализовать данные SQL для финального LLM-ответа');
        }

        $response = self::buildModel($systemInstructionText)->generateContent([
            "Сформируй ответ для пользователя на основе данных:\n{$payload}",
        ]);

        return self::extractResponseText($response);
    }
}
