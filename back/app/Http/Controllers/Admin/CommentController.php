<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\CallResource;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Utils\CallCommentSuggest;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CommentController extends Controller
{
    protected $filters = [
        'equals' => ['entity_id', 'entity_type', 'extra'],
    ];

    public function index(Request $request)
    {
        $query = Comment::with('user');

        $this->filter($request, $query);

        if ($request->has('tab_counts')) {
            return $query->get()->countBy('extra')->all();
        }

        if (! $request->has('extra')) {
            $query->whereNull('extra');
        }

        return $this->handleIndexRequest($request, $query, CommentResource::class);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'text' => ['required', 'string', 'max:1000'],
            'entity_id' => ['required', 'integer'],
            'entity_type' => ['required', 'string'],
            'extra' => ['nullable', 'string', 'max:20'],
        ]);

        // Комментарии больше не отличаются по источнику (ручной/из auto-suggest).
        $comment = Comment::create([
            'text' => $validated['text'],
            'entity_id' => (int) $validated['entity_id'],
            'entity_type' => $validated['entity_type'],
            'extra' => $validated['extra'] ?? null,
        ]);

        return new CommentResource($comment);
    }

    /**
     * Auto-suggest комментария по последнему звонку текущего менеджера.
     */
    public function autoSuggest(Request $request): ?CallResource
    {
        $validated = $request->validate([
            'entity_id' => ['required', 'integer'],
            'entity_type' => ['required', 'string'],
        ]);

        $call = CallCommentSuggest::findLastCallForEntity(
            userId: (int) auth()->id(),
            entityType: $validated['entity_type'],
            entityId: (int) $validated['entity_id'],
        );

        if (! $call) {
            return null;
        }

        return new CallResource($call);
    }

    /**
     * Подтянуть актуальный auto-suggest и вставить текст в textarea.
     */
    public function autoSuggestText(Request $request): array
    {
        $validated = $request->validate([
            'entity_id' => ['required', 'integer'],
            'entity_type' => ['required', 'string'],
            'call_id' => ['required', 'integer'],
        ]);

        $userId = (int) auth()->id();
        $latestCall = CallCommentSuggest::findLatestUserCall($userId);
        $requestedCallId = (int) $validated['call_id'];

        // Если за время между открытием и кликом появился новый звонок, старая подсказка неактуальна.
        if (! $latestCall || $latestCall->id !== $requestedCallId) {
            throw ValidationException::withMessages([
                'call_id' => 'Данные устарели, обновите страницу',
            ]);
        }

        // Повторно проверяем, что звонок всё ещё подходит текущей сущности.
        $actualCall = CallCommentSuggest::findLastCallForEntity(
            userId: $userId,
            entityType: $validated['entity_type'],
            entityId: (int) $validated['entity_id'],
        );
        if (! $actualCall || $actualCall->id !== $requestedCallId) {
            throw ValidationException::withMessages([
                'call_id' => 'Данные устарели, обновите страницу',
            ]);
        }

        $summary = trim((string) $actualCall->summary);
        if ($summary === '') {
            throw ValidationException::withMessages([
                // Резервная защита от гонок: в UI это состояние теперь показывается live через SSE.
                'summary' => 'Звонок ещё обрабатывается, дождитесь обновления кнопки.',
            ]);
        }

        return [
            'text' => $summary,
        ];
    }

    public function update(Comment $comment, Request $request)
    {
        abort_if($comment->user_id !== auth()->id(), 403);
        $request->validate([
            'text' => ['required', 'string', 'max:1000'],
        ]);

        // Для редактирования разрешаем менять только текст.
        $comment->update($request->only(['text']));

        return new CommentResource($comment);
    }

    public function destroy(Comment $comment)
    {
        abort_if($comment->user_id !== auth()->id(), 403);
        $comment->delete();
    }
}
