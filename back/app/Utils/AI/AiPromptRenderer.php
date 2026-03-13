<?php

namespace App\Utils\AI;

use App\Models\AiPrompt;
use Illuminate\Support\Facades\Blade;
use RuntimeException;

class AiPromptRenderer
{
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
        return $this->renderInstructionById($id, [...$cleanScope, ...$data]);
    }

    /**
     * Рендер instruction по фиксированному ID.
     *
     * @param  array<string, mixed>  $data
     */
    private function renderInstructionById(int $id, array $data = []): string
    {
        if (isset($this->renderStack[$id])) {
            throw new RuntimeException("Обнаружена рекурсия при рендере ai_prompts.id={$id}");
        }

        $this->renderStack[$id] = true;

        try {
            $instruction = AiPrompt::findOrFail($id)->instruction;
            if (! is_string($instruction) || trim($instruction) === '') {
                throw new RuntimeException("AI instruction не найден или пустой (ai_prompts.id={$id})");
            }

            return Blade::render($instruction, $data);
        } finally {
            unset($this->renderStack[$id]);
        }
    }

    /**
     * Рендер пары instruction + prompt по фиксированному ID.
     *
     * @param  array<string, mixed>  $data
     * @return array{0: string, 1: string}
     */
    public function renderInstructionAndPromptById(int $id, array $data = []): array
    {
        return $this->renderPromptPairById($id, $data);
    }

    /**
     * Единая точка рендера пары instruction + prompt.
     *
     * @param  array<string, mixed>  $data
     * @return array{0: string, 1: string}
     */
    private function renderPromptPairById(int $id, array $data = []): array
    {
        if (isset($this->renderStack[$id])) {
            throw new RuntimeException("Обнаружена рекурсия при рендере ai_prompts.id={$id}");
        }

        $this->renderStack[$id] = true;

        try {
            $aiPrompt = AiPrompt::findOrFail($id);
            $instruction = $aiPrompt->instruction;
            $prompt = $aiPrompt->prompt;

            if (! is_string($instruction) || trim($instruction) === '') {
                throw new RuntimeException("AI instruction не найден или пустой (ai_prompts.id={$id})");
            }

            if (! is_string($prompt) || trim($prompt) === '') {
                throw new RuntimeException("AI prompt не найден или пустой (ai_prompts.id={$id})");
            }

            return [
                Blade::render($instruction, $data),
                Blade::render($prompt, $data),
            ];
        } finally {
            unset($this->renderStack[$id]);
        }
    }
}
