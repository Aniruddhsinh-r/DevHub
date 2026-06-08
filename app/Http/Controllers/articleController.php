<?php

namespace App\Http\Controllers;

use App\Actions\CreateArticle;
use App\Actions\UpdateArticle;
use App\Http\Requests\ArticleValidationRequest;
use App\Models\Article;
use App\Models\Bookmark;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Like;
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

        $articles = Article::with(['user', 'category'])
        ->where('status', 'published')
        ->where(function($query) use ($search) {
            $query->where('title', 'LIKE', "%{$search}%")
                  ->orWhere('excerpt', 'LIKE', "%{$search}%")
                  ->orWhere('body', 'LIKE', "%{$search}%");
        })->latest()->paginate(9);

        return view('articles.article',[
            'articles' => $articles
        ]);
    }

    public function home() {
        $articles = Article::where('status','published')->latest()->take(3)->get();

        if (Auth()->user()?->role === 'admin') {
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
        $action->handle($request->safe()->all());
        return to_route('articles.index')->with('success', 'Article created successfully.');
    }

    public function show(Article $article)
    {
        $viewed = View::where(['user_id' => Auth::id(), 'article_id' => $article->id, 'viewed' => true])->exists();
        $comments = Comment::where('article_id',$article->id)->whereNull('parent_id')->with('replies')->get();
        $replies = Comment::where('article_id',$article->id)->whereNotNull('parent_id')->get();

        if (!$viewed) {
            Article::where('id', $article->id)->increment('view_count');
            View::insert(['user_id' => Auth::id(), 'article_id' => $article->id, 'viewed' => true, 'created_at' => now()]);
        }
        return view('articles.show', ['article' => $article, 'comments' => $comments, 'replies' => $replies]);
    }

    public function published()
    {
        $user_id = Auth::id();
        $articles = Article::where('user_id',$user_id)->latest()->get();

        return view('articles.myArticles', ['articles' => $articles]);
    }

    public function userpublished(User $user) {
        $user_id = $user;
        $articles = $user_id->articles()->latest()->get();

        return view('articles.userArticles', ['articles' => $articles]);
    }

    public function showDraftArticle(User $user) {
        if(Auth::id() == $user->id){
            $articles = Article::with(['user', 'category'])->where(['user_id' => $user->id,'status' => 'draft'])->latest()->get();
            return view('components.draftArticle',['articles' => $articles]);
        }
        return back()->with('error','you cant see others draft.');
    }

    public function userarticleshow(User $user) {
        if (Auth::id() == $user->id) {
            $user = Auth::user();
            // return to_route('publishedarticle');
            return view('auth.myprofile', ['user' => $user]);
        }

        $articles = Article::with(['user', 'category'])->where('user_id', $user->id)->latest()->get();
        return view('articles.article',[
            'articles' => $articles
        ]);
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
        $action->handle($request->safe()->all(), $article);
        return to_route('publishedarticle')->with('success', 'Article updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Article $article)
    {
        $this->authorize('delete', $article);

        View::where('article_id',$article->id)->delete();
        Comment::where('article_id',$article->id)->delete();
        Bookmark::where('article_id',$article->id)->delete();
        Like::where('article_id',$article->id)->delete();
        $article->delete();

        return back()->with('success','article delete successfully.');
    }
}
