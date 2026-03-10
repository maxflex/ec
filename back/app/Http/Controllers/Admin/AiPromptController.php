<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiPrompt;
use App\Utils\AI\GeminiFileService;
use Illuminate\Http\Request;

class AiPromptController extends Controller
{
    public function index(Request $request)
    {
        // Для списка оставляем только id/title, чтобы не тянуть большие тексты.
        $query = AiPrompt::query()
            ->select(['id', 'title', 'files'])
            ->orderBy('id');

        return $this->handleIndexRequest($request, $query);
    }

    public function show(AiPrompt $aiPrompt)
    {
        return $aiPrompt;
    }

    public function update(AiPrompt $aiPrompt, Request $request)
    {
        // Сохраняем предыдущее состояние файлов, чтобы удалить лишние uri из Gemini при удалении/замене.
        $previousPromptFiles = $aiPrompt->files;
        $aiPrompt->update($request->only(['title', 'instruction', 'prompt', 'files']));
        // При сохранении шаблона сразу синхронизируем вложения в Gemini Files API.
        $aiPrompt = GeminiFileService::syncPromptFiles($aiPrompt, $previousPromptFiles);

        return $aiPrompt;
    }
}
