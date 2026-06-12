<?php

namespace App\Http\Controllers;

use App\Actions\CreateArticle;
use App\Actions\UpdateArticle;
use App\Http\Requests\ArticleValidationRequest;
use App\Models\Article;
use App\Models\Category;
use App\Models\Comment;
use App\Models\User;
use App\Models\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ArticleController extends Controller
{
    use AuthorizesRequests;
    public function index(Request $request) {
        $search = $request->search;

        $articles = Article::query()->where('status', 'published')
            ->when($search, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('title', 'like', "%{$search}%")
                          ->orWhere('excerpt', 'like', "%{$search}%")
                          ->orWhere('body', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(9);

        return view('articles.article',['articles' => $articles]);
    }

    public function home() {
        $articles = Article::where('status','published')->latest()->take(3)->get();

        if (Auth::user()?->hasRole('admin')) {
            return to_route('admin.dashboard');
        }
        return view('components.home', ['articles' => $articles]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', Article::class);
        $categories = Category::all();

        return view('articles.articleForm', [
            'article' => new Article(),
            'categories' => $categories
        ]);
    }

    public function store(ArticleValidationRequest $request, CreateArticle $action)
    {
        $this->authorize('publish', Article::class);

        $action->handle($request->validated());
        return to_route('articles.index')->with('success', 'Article created successfully.');
    }

    public function show(Article $article)
    {
        $viewed = Auth::user()->views()->where('article_id', $article->id)->exists();
        $comments = $article->comments()->whereNull('parent_id')->with(['user', 'replies.user'])->get();

        if (Auth::check() && Auth::id() !== $article->user_id && !$viewed) {
            Article::where('id', $article->id)->increment('view_count');
            View::create(['user_id' => Auth::id(), 'article_id' => $article->id]);
        }
        return view('articles.show', ['article' => $article, 'comments' => $comments,]);
    }

    public function myArticles()
    {
        $articles = Article::where('user_id',Auth::id())->latest()->get();

        return view('articles.myArticles', ['articles' => $articles]);
    }

    public function userpublished(User $user) {
        $articles = $user->articles()->where('status', 'published')->latest()->get();

        return view('articles.userArticles', ['articles' => $articles]);
    }

    public function draftArticle() {
        $user = Auth::user();
        $articles = $user->articles()->where('status', 'draft')->latest()->get();
        // Article::with(['user', 'category'])->where(['user_id' => $user,'status' => 'draft'])->latest()->get();

        return view('components.draftArticle',['articles' => $articles]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Article $article)
    {
        $this->authorize('update', $article);

        $categories = Category::all();
        return view('articles.articleForm', compact('article','categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ArticleValidationRequest $request, Article $article, UpdateArticle $action)
    {
        $this->authorize('update', $article);
        if ($request->isPrecognitive()) {
            return response()->json(['valid' => true]);
        }
        $action->handle($request->validated(), $article);
        return to_route('publishedarticle')->with('success', 'Article updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Article $article)
    {
        $this->authorize('delete', $article);

        $article->likes()->delete();
        $article->delete();
        $article->bookmarks()->detach();
        View::where('article_id', $article->id)->delete();
        $article->delete();

        return back()->with('success', 'Article deleted successfully.');
    }
}
