<?php

namespace App\Http\Controllers;

use App\Actions\CreateArticle;
use App\Actions\UpdateArticle;
use App\Http\Requests\ArticleValidationRequest;
use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;
use Illuminate\Notifications\Action;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Storage;

class articleController extends Controller
{
    public function index()
    {
        $categories = Category::all();

        return view('articles.articleForm', [
            'article' => new Article(),
            'categories' => $categories
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(ArticleValidationRequest $request, CreateArticle $action)
    {
        if (Auth::check()) {
            $action->handle($request->safe()->all());
            return to_route('home')->with('success', 'Article created successfully.');
        }
        return to_route('register')->with('error','You must be authorize before posting artical');
    }

    public function store(Article $article)
    {

    }

    public function show(Article $article)
    {
        if (Auth::check()) {
            $viewed = DB::table('views')->where(['user_id' => Auth::id(), 'article_id' => $article->id, 'viewed' => true])->exists();

            if (!$viewed) {
                DB::table('articles')->where('id', $article->id)->increment('view_count');
                DB::table('views')->insert(['user_id' => Auth::id(), 'article_id' => $article->id, 'viewed' => true, 'created_at' => now()]);
            }
            return view('articles.show', ['article' => $article]);
        }
        return view('auth.register')->with('Please Register to see full article.');
    }

    public function published(Article $article)
    {
        // $id = Auth::id();
        // $articles = DB::table('articles')->where('user_id', $id)->get();

        $articles = Auth::user()->articles()->latest()->get();

        if (Auth::check()) {
            return view('articles.userArticles', ['articles' => $articles]);
        }
        return view('auth.register')->with('Please Register to see articles.');
    }

    /**
     * Display the specified resource.
     */
    public function displayArticle()
    {
        $articles = Article::with(['user', 'category'])->where('status', 'published')->latest()->get();
        return view('articles.article',[
            'articles' => $articles
        ]);
    }

    public function showDraftArticle() {
        $id = Auth::id();
        $articles = Article::with(['user', 'category'])->where(['user_id' => $id,'status' => 'draft'])->latest()->get();
        return view('components.home',[
            'articles' => $articles
        ]);
    }

    public function userarticleshow(User $user) {
        if (Auth::check()) {
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
        return view('auth.register')->with('Please Register to see user articles.');
    }
    /**
     * Show the form for editing the specified resource.
     */
    public function editArticle(Article $article)
    {
        $categories = Category::all();

        return view('articles.articleForm', compact('article','categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ArticleValidationRequest $request, Article $article, UpdateArticle $action)
    {
        if (Auth::check()) {
            $action->handle($request->safe()->all(), $article);
            return to_route('publishedarticle')->with('success', 'Article updated successfully.');
        }
        return to_route('showArticle')->with('error','You must be authorize before posting artical');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Article $article)
    {
        if (Auth::check()) {
            if ($article->cover_path) {
                Storage::disk('public')->delete($article->cover_path);
            }
            // $article->delete();
            return back()->with('success','article delete successfully.');
        }
    }
}
