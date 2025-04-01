<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    protected $filters = [
        'equals' => ['entity_id', 'entity_type'],
    ];

    public function index(Request $request)
    {
        $query = Comment::with('user');
        $this->filter($request, $query);

        return $this->handleIndexRequest($request, $query, CommentResource::class);
    }

    public function store(Request $request)
    {
        $comment = auth()->user()->comments()->create(
            $request->all()
        );

        return new CommentResource($comment);
    }

    public function update(Comment $comment, Request $request)
    {
        abort_if($comment->user_id !== auth()->id(), 403);
        $request->validate([
            'text' => ['required', 'string'],
        ]);

        $comment->update($request->all());

        return new CommentResource($comment);
    }

    public function destroy(Comment $comment)
    {
        abort_if($comment->user_id !== auth()->id(), 403);
        $comment->delete();
    }
}
