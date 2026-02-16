<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiPrompt;
use Illuminate\Http\Request;

class AiPromptController extends Controller
{
    public function index(Request $request)
    {
        // Для списка оставляем только id/title, чтобы не тянуть большие тексты.
        $query = AiPrompt::query()
            ->select(['id', 'title'])
            ->orderBy('id');

        return $this->handleIndexRequest($request, $query);
    }

    public function show(AiPrompt $aiPrompt)
    {
        return $aiPrompt;
    }

    public function update(AiPrompt $aiPrompt, Request $request)
    {
        $aiPrompt->update($request->only(['title', 'text']));

        return $aiPrompt;
    }
}
