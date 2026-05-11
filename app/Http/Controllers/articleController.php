<?php

namespace App\Http\Controllers;

use App\Actions\CreateArticle;
use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;

class articleController extends Controller
{
    public function index()
    {
        $categories = Category::all();

        return view('articles.articleForm', [
            'categories' => $categories
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request, CreateArticle $action)
    {
        $validation = $request->validate([
            'title' => 'required|max:255|min:6',
            'excerpt' => 'required|max:600|min:10',
            'body' => 'required|min:30|max:50000',
            'category_id' => 'required|exists:categories,id',
            'status' => 'required',
            'scheduled_hours' => 'nullable|integer|min:1|max:48',
            'scheduled_minutes' => 'required_if:status,scheduled|nullable|integer|min:1|max:59',
            'cover_path' => 'nullable|image|',
        ]);

        if (Auth::check()) {
            $action->handle($validation);
            return to_route('home')->with('success', 'Article created successfully.');
        }
        return to_route('register')->with('error','You must be authorize before posting artical');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Article $article)
    {

    }

    public function show(Article $article)
    {
        $sessionKey = 'viewed_article_' . $article->id;
        if (Auth::user()->id && !session()->has($sessionKey)) {
            DB::table('articles')->where('id', $article->id)->increment('view_count');
            session()->put($sessionKey, true);
        }
        return view('articles.show', [
            'article' => $article,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function showallarticle()
    {
        $articles = Article::with(['user', 'category'])->where('status', 'published')->latest()->get();
        return view('articles.article',[
            'articles' => $articles
        ]);
    }

    public function showDraftArticle() {
        $articles = Article::with(['user', 'category'])->where('status', 'draft')->latest()->get();
        return view('components.home',[
            'articles' => $articles
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function editArticle(Article $article)
    {

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
