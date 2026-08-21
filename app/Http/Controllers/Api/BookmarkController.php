<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class BookmarkController extends Controller
{
    public function store(string $slug)
    {
        $article = Article::where('slug', $slug)->first();
        Gate::authorize('bookmark', $article);

        if (! $article) {
            return response()->json(['message' => 'Article not found.',], 404);
        }

        $bookmark = $article->bookmark()->where('user_id', Auth::id())->first();

        if ($bookmark) {
            return response()->json([
                'message' => 'Article is already bookmarked.',
                'bookmark' => $bookmark,
            ], 409);
        }

        $bookmark = $article->bookmark()->create([
            'user_id' => Auth::id(),
        ]);

        return response()->json([
            'message' => 'Article bookmarked successfully.',
            'bookmark' => $bookmark,
        ], 201);
    }

    public function destroy(string $slug)
    {
        $article = Article::where('slug', $slug)->first();
        Gate::authorize('bookmark', $article);

        if (! $article) {
            return response()->json([
                'message' => 'Article not found.',
            ], 404);
        }

        $bookmark = $article->bookmark()
            ->where('user_id', Auth::id())
            ->first();

        if (! $bookmark) {
            return response()->json([
                'message' => 'Article is not bookmarked.',
            ], 404);
        }

        $bookmark->delete();

        return response()->json([
            'message' => 'Article bookmark removed successfully.',
        ], 200);
    }
}