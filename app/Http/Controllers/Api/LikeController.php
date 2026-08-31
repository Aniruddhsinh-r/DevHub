<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class LikeController extends Controller
{
    public function store(string $slug)
    {
        $article = Article::where('slug', $slug)->first();

        if (! $article) {
            return response()->json(['message' => 'Article not found.'], 404);
        }
        Gate::authorize('like', $article);

        try {
            $like = $article->likes()->create(['user_id' => Auth::id()]);
        } catch (QueryException $e) {
            if ($e->getCode() === '23000') { // integrity constraint violation
                return response()->json(['message' => 'Article is already liked.'], 409);
            }
            throw $e;
        }

        return response()->json([
            'message' => 'Article liked successfully.',
            'like' => $like,
        ], 201);
    }

    public function destroy(string $slug)
    {
        $article = Article::where('slug', $slug)->first();

        if (! $article) {
            return response()->json([
                'message' => 'Article not found.',
            ], 404);
        }
        Gate::authorize('like', $article);

        $deleted = $article->likes()
            ->where('user_id', Auth::id())
            ->delete();

        if (! $deleted) {
            return response()->json([
                'message' => 'You have not liked this article.',
            ], 404);
        }

        return response()->json([
            'message' => 'Article unliked successfully.',
        ], 200);
    }
}
