<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class CommentController extends Controller
{
    public function store(Request $request, string $slug)
    {
        $article = Article::where('slug', $slug)->first();

        if (! $article) {
            return response()->json(['message' => 'Article not found.'], 404);
        }
        Gate::authorize('comment', $article);

        $values = $request->validate([
            'body' => ['required', 'string', 'min:1', 'max:1000'],
        ]);

        $comment = $article->comments()->create([
            'user_id' => Auth::id(),
            'body' => $values['body'],
        ]);

        return response()->json([
            'message' => 'Comment added successfully.',
            'comment' => $comment,
        ], 201);
    }

    public function reply(Request $request, string $slug)
    {
        $article = Article::where('slug', $slug)->first();

        if (! $article) {
            return response()->json(['message' => 'Article not found.'], 404);
        }
        Gate::authorize('comment', $article);

        $values = $request->validate([
            'body' => ['required', 'string', 'min:1', 'max:1000'],
            'parent_id' => ['required', 'integer',
                Rule::exists('comments', 'id')->where(
                    fn ($query) => $query->where('article_id', $article->id)
                ),
            ],
        ]);

        $reply = $article->comments()->create([
            'user_id' => Auth::id(),
            'body' => $values['body'],
            'parent_id' => $values['parent_id'],
        ]);

        return response()->json([
            'message' => 'Reply added successfully.',
            'comment' => $reply,
        ], 201);
    }
}
