<?php

namespace App\Utils\AI;

use App\Models\AiPrompt;
use Illuminate\Support\Facades\Blade;
use RuntimeException;

class AiPromptRenderer
{
    /**
     * Кэш instruction в рамках одного запроса.
     *
     * @var array<int, ?string>
     */
    private array $instructionCache = [];

    /**
     * Кэш prompt в рамках одного запроса.
     *
     * @var array<int, string>
     */
    private array $promptCache = [];

    /**
     * Стек рендера для защиты от рекурсивных ссылок (prompt A -> prompt B -> prompt A).
     *
     * @var array<int, bool>
     */
    private array $renderStack = [];

    /**
     * Версия для blade-директивы: объединяет локальную область видимости шаблона и явные параметры.
     *
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $scope
     */
    public function renderByIdWithScope(int $id, array $data = [], array $scope = []): string
    {
        // Удаляем служебные переменные blade/laravel из scope.
        $cleanScope = collect($scope)
            ->reject(fn (mixed $v, string|int $k): bool => ! is_string($k) || str_starts_with($k, '__'))
            ->all();

        // Явно переданные данные имеют приоритет над переменными из scope.
        return $this->renderById($id, [...$cleanScope, ...$data]);
    }

    /**
     * Рендер instruction по фиксированному ID.
     *
     * @param  array<string, mixed>  $data
     */
    public function renderById(int $id, array $data = []): string
    {
        if (isset($this->renderStack[$id])) {
            throw new RuntimeException("Обнаружена рекурсия при рендере ai_prompts.id={$id}");
        }

        $this->renderStack[$id] = true;

        try {
            return Blade::render($this->getInstruction($id), $data);
        } finally {
            unset($this->renderStack[$id]);
        }
    }

    /**
     * Рендер пары instruction + prompt по фиксированному ID.
     *
     * @param  array<string, mixed>  $data
     * @return array{0: ?string, 1: string}
     */
    public function renderInstructionAndPromptById(int $id, array $data = []): array
    {
        if (isset($this->renderStack[$id])) {
            throw new RuntimeException("Обнаружена рекурсия при рендере ai_prompts.id={$id}");
        }

        $this->renderStack[$id] = true;

        try {
            $instruction = $this->getNullableInstruction($id);

            return [
                $instruction === null ? null : Blade::render($instruction, $data),
                Blade::render($this->getPrompt($id), $data),
            ];
        } finally {
            unset($this->renderStack[$id]);
        }
    }

    private function getPrompt(int $id): string
    {
        if (array_key_exists($id, $this->promptCache)) {
            return $this->promptCache[$id];
        }

        $prompt = AiPrompt::findOrFail($id)->prompt;
        if (! is_string($prompt) || trim($prompt) === '') {
            throw new RuntimeException("AI prompt не найден или пустой (ai_prompts.id={$id})");
        }

        $this->promptCache[$id] = $prompt;

        return $prompt;
    }

    private function getInstruction(int $id): string
    {
        if (array_key_exists($id, $this->instructionCache)) {
            return $this->instructionCache[$id];
        }

        $instruction = $this->getNullableInstruction($id);
        if (! is_string($instruction) || trim($instruction) === '') {
            throw new RuntimeException("AI instruction не найден или пустой (ai_prompts.id={$id})");
        }

        $this->instructionCache[$id] = $instruction;

        return $instruction;
    }

    private function getNullableInstruction(int $id): ?string
    {
        if (array_key_exists($id, $this->instructionCache)) {
            return $this->instructionCache[$id];
        }

        $instruction = AiPrompt::findOrFail($id)->instruction;
        $instruction = is_string($instruction) && trim($instruction) !== '' ? $instruction : null;

        $this->instructionCache[$id] = $instruction;

        return $instruction;
    }
}
