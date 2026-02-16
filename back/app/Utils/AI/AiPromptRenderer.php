<?php

namespace App\Utils\AI;

use App\Models\AiPrompt;
use Illuminate\Support\Facades\Blade;

class AiPromptRenderer
{
    /**
     * Кэш промптов в рамках одного запроса, чтобы не делать лишние запросы в БД.
     *
     * @var array<int, string>
     */
    private array $templateCache = [];

    /**
     * Стек рендера для защиты от рекурсивных ссылок (prompt A -> prompt B -> prompt A).
     *
     * @var array<int, bool>
     */
    private array $renderStack = [];

    /**
     * Рендер prompt по alias-имени, которое удобно использовать в blade.
     *
     * @param  array<string, mixed>  $data
     */
    public function renderAlias(string $alias, array $data = []): string
    {
        return $this->renderById(AiPrompt::resolveAlias($alias), $data);
    }

    /**
     * Версия для blade-директивы: объединяет локальную область видимости шаблона и явные параметры.
     *
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $scope
     */
    public function renderAliasWithScope(string $alias, array $data = [], array $scope = []): string
    {
        // Удаляем служебные переменные blade/laravel из scope.
        $cleanScope = collect($scope)
            ->reject(fn (mixed $v, string|int $k): bool => ! is_string($k) || str_starts_with($k, '__'))
            ->all();

        // Явно переданные данные имеют приоритет над переменными из scope.
        return $this->renderAlias($alias, [...$cleanScope, ...$data]);
    }

    /**
     * Рендер prompt по фиксированному ID.
     *
     * @param  array<string, mixed>  $data
     */
    public function renderById(int $id, array $data = []): string
    {
        if (isset($this->renderStack[$id])) {
            throw new \RuntimeException("Обнаружена рекурсия при рендере ai_prompts.id={$id}");
        }

        $this->renderStack[$id] = true;

        try {
            return Blade::render($this->getTemplate($id), $data);
        } finally {
            unset($this->renderStack[$id]);
        }
    }

    private function getTemplate(int $id): string
    {
        if (array_key_exists($id, $this->templateCache)) {
            return $this->templateCache[$id];
        }

        $text = AiPrompt::query()->find($id)?->text;
        if (! is_string($text) || $text === '') {
            throw new \RuntimeException("AI prompt не найден или пустой (ai_prompts.id={$id})");
        }

        $this->templateCache[$id] = $text;

        return $text;
    }
}
