<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Bookmark;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Like;
use App\Models\User;
use App\Models\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    public function index() {
        $articles = Article::all();
        $users = User::whereNull('deleted_at')->get();
        $comments = Comment::get();
        $views = View::get();
        $likes = Like::get();
        $topUser = User::withCount('articles')
        ->orderByDesc('articles_count')
        ->first();

        return view('admin.dashboard',['articles' => $articles,'users' => $users,'comments' => $comments,'views'=>$views,'likes'=>$likes,'topUser'=>$topUser]);
    }

    public function create(Request $request) {
        $request->validate([
            'name' => ['required','min:3','max:20']
        ]);

        $name = $request->name;
        $slug = Str::slug($name, '-');

        if (Auth::check() && Auth::user()->role === 'admin') {
            Category::create([
                'name' => $name,
                'slug' => $slug,
                'created_at' => now(),
            ]);

            return back()->with('success','Category create successfully.');
        }
        return view('auth.register')->with('error','Only admin can create categories.');
    }

    public function showuser(User $user) {

        $articles = Article::with(['user', 'category'])->where('user_id', $user->id)->latest()->get();
        return view('admin.users.userProfile', ['user'=>$user,'articles'=>$articles]);
    }

    public function userPublished(User $user) {
        $articles = Article::with(['category'])->where('user_id', $user->id)->latest()->get();
        return view('admin.users.userPublished', ['articles'=>$articles]);

    }

    public function show() {
        $categories = Category::withCount('articles')->latest()->paginate(6);

        return view('admin.categories', ['categories' => $categories]);
    }

    public function user(Request $request) {
        $search = $request->search;
        $users = User::where('role','author')->where('name', 'LIKE', "%{$search}%")->latest()->paginate(6);

        return view('admin.users.users', ['users'=>$users]);
    }

    public function userRemove(User $user) {

        if (Auth::check() && Auth::user()->role === 'admin') {
            DB::transaction(function () use ($user) {
                $user->views()->delete();
                $user->comments()->delete();
                $user->bookmarks()->delete();
                $user->likes()->delete();
                $user->articles()->delete();
                $user->delete();
            });

            return back()->with('success','User remove successfully.');
        }
        return back()->with('error','Only admin can perform this task');
    }

    public function articles(Request $request) {
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

        return view('admin.articles.articles',['articles'=>$articles]);
    }

    public function destroy(Category $category) {
        if (Auth::check() && Auth::user()->role === 'admin') {
            $articles = Article::where('category_id', $category->id)->get(['id']);

            foreach ($articles as $article) {
                Like::where('article_id',$article->id)->delete();
                Comment::where('article_id',$article->id)->delete();
                View::where('article_id',$article->id)->delete();
                Bookmark::where('article_id',$article->id)->delete();
            }
            Article::where('category_id',$category->id)->delete();
            Category::where('id',$category->id)->delete();
            return back()->with('success','Category deleted successfully');
        }
        return back()->with('error','Only admin can perform this task');
    }

    public function showArticle(Article $article) {
        // $article = Article::where('id',$article->id)->get();
        $comments = Comment::where('article_id',$article->id)->whereNull('parent_id')->with('replies')->get();
        $replies = Comment::where('article_id',$article->id)->whereNotNull('parent_id')->get();

        $likes = Like::where('article_id',$article)->get();

        return view('admin.articles.article', ['article' => $article, 'comments' => $comments, 'replies' => $replies, 'likes' => $likes]);
    }
}
