<?php

namespace App\Http\Controllers\Api;

use App\Enums\ArticleStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Article;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ArticleController extends Controller
{
    public function create(Request $request)
    {
        Gate::authorize('create', Article::class);

        $values = $request->validate([
            'title' => ['required','string','min:6','max:50',],
            'excerpt' => ['required','string','min:10','max:255',],
            'body' => ['required','string','min:30','max:50000',],
            'category_id' => ['required','exists:categories,id',],
            'status' => ['required',Rule::enum(ArticleStatus::class),],
            'duration' => ['required_if:status,' . ArticleStatus::SCHEDULED->value,'nullable','date','after_or_equal:now','before_or_equal:' . now()->addHours(48)->toDateTimeString(),],
            'cover_path' => ['nullable','image','mimes:jpeg,png,jpg,webp','max:2048',],
        ]);

        $title = $values['title'];

        $data = collect($values)->only([
            'title', 'excerpt', 'body', 'category_id', 'status',
        ])->toArray();

        $base = Str::slug($title, '-');
        $slug = $base;
        $count = 2;

        while (Article::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $count;
            $count++;
        }

        $data['slug'] = $slug;

        if ($request->hasFile('cover_path')) {
            $data['cover_path'] = $request->file('cover_path')->store('articleCovers', 'public');
        }

        if ($values['status'] === ArticleStatus::SCHEDULED->value) {
            $data['duration'] = $values['duration'];
        } elseif ($values['status'] === ArticleStatus::PUBLISHED->value) {
            $data['published_at'] = now();
        } else {
            $data['published_at'] = null;
        }

        $article = Auth::user()->articles()->create($data);

        return response()->json([
            'message' => 'Article created successfully.',
            'article' => $article,
        ], 201);
    }

    public function update(Request $request, string $slug)
    {
        $article = Article::where('slug', $slug)->first();

        if (! $article) {
            return response()->json(['message' => 'record not found.'], 404);
        }

        Gate::authorize('update', $article);

        $values = $request->validate([
            'title' => ['required','string','min:6','max:50',],
            'excerpt' => ['required','string','min:10','max:255',],
            'body' => ['required','string','min:30','max:50000',],
            'category_id' => ['required','exists:categories,id',],
            'status' => ['required',Rule::enum(ArticleStatus::class),],
            'duration' => ['required_if:status,' . ArticleStatus::SCHEDULED->value,'nullable','date','after_or_equal:now','before_or_equal:' . now()->addHours(48)->toDateTimeString(),],
            'cover_path' => ['nullable','image','mimes:jpeg,png,jpg,webp','max:2048',],
        ]);

        $data = collect($values)->only([
            'title', 'excerpt', 'body', 'category_id', 'status', 'duration'
        ])->toArray();

        if ($request->hasFile('cover_path')) {
            if ($article->cover_path) {
                Storage::disk('public')->delete($article->cover_path);
            }

            $data['cover_path'] = $request->file('cover_path')->store('articleCovers', 'public');
        } elseif ($request->input('remove_cover_path') === 'true') {
            if ($article->cover_path) {
                Storage::disk('public')->delete($article->cover_path);
            }

            $data['cover_path'] = null;
        }

        if (! empty($data['title'])) {
            $base = Str::slug($data['title'], '-');
            $slug = $base;
            $count = 2;

            while (Article::where('slug', $slug)->where('id', '!=', $article->id)->exists()) {
                $slug = $base.'-'.$count;
                $count++;
            }

            $data['slug'] = $slug;
        }

        if ($data['status'] === ArticleStatus::SCHEDULED->value) {
            $data['published_at'] = null;
        } elseif ($data['status'] === ArticleStatus::PUBLISHED->value) {
            $data['duration'] = null;
            $data['published_at'] = $article->published_at ?? now();
        } else {
            $data['duration'] = null;
            $data['published_at'] = null;
        }

        $article->update($data);

        return response()->json([
            'message' => 'Article updated successfully.',
            'article' => $article,
        ], 200);
    }

    public function delete(string $slug)
    {
        $article = Article::where('slug', $slug)->first();

        if (! $article) {
            return response()->json(['message' => 'record not found.'], 404);
        }

        Gate::authorize('delete', $article);

        if ($article->cover_path) {
            Storage::disk('public')->delete($article->cover_path);
        }

        $article->delete();

        return response()->json([
            'message' => 'Article deleted successfully.',
        ], 200);
    }

    public function forceDelete(string $slug)
    {
        $article = Article::withTrashed()->where('slug', $slug)->first();

        if (! $article) {
            return response()->json(['message' => 'Article not found.',], 404);
        }

        Gate::authorize('forceDelete', $article);
        $article->forceDelete();

        if (! empty($article->cover_path)) {
            Storage::disk('public')->delete($article->cover_path);
        }

        return response()->json([
            'message' => 'Article permanently deleted successfully.',
        ], 200);
    }

    public function adminArticles(Request $request)
    {
        if (! Auth::user()?->hasRole([UserRole::ADMIN,UserRole::SUPERADMIN])) {
            return response()->json(['message' => 'This action is unauthorized.'], 403);
        }

        $articles = Article::query()
            ->when($request->status, function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->when($request->search, function ($q) use ($request) {
                $q->where(function ($query) use ($request) {
                    $query
                        ->where('title', 'like', "%{$request->search}%")
                        ->orWhere('excerpt', 'like', "%{$request->search}%");
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 12));

        return response()->json($articles);
    }

    public function index(Request $request)
    {
        if (! Auth::user()?->hasRole([UserRole::AUTHOR])) {
            return response()->json(['message' => 'This action is unauthorized.'], 403);
        }

        $articles = Article::query()
            ->where('status', 'published')
            ->when($request->search, function ($q) use ($request) {
                $q->where(function ($query) use ($request) {
                    $query
                        ->where('title', 'like', "%{$request->search}%")
                        ->orWhere('excerpt', 'like', "%{$request->search}%");
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 12));

        return response()->json($articles);
    }

    public function myArticle()
    {
        if(! Auth::user()){
            return response()->json(['message' => 'This action is unauthorized.'], 403);
        }

        $articles = Auth::user()->articles()->with(['category','views'])->latest()->paginate(12);

        return response()->json([
            'bookmarks' => $articles,
        ], 200);
    }

    public function show(string $slug)
    {
        $article = Article::with(['category', 'category', 'user', 'likes', 'views'])->where('slug', $slug)
            ->where(function ($query) {
                $query->where('status', ArticleStatus::PUBLISHED)->orWhere('user_id', Auth::id());
            })->first();

        if (! $article) {
            return response()->json(['message' => 'record not found.',], 404);
        }

        $user = Auth::user();

        return response()->json([
            'article' => $article,
            'is_liked' => $user ? $article->likes()->where('user_id', $user->id)->exists() : false,
            'is_bookmarked' => $user ? $article->bookmarks()->where('user_id', $user->id)->exists() : false,
            'comments' => $article->comments() ->latest() ->get(),
            'likes_count' => $article->likes()->count(),
            'comments_count' => $article->comments()->count(),
        ]);
    }
}
