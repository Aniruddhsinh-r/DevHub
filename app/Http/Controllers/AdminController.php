<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use App\Models\comments;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    public function index() {
        $articles = DB::table('articles')->whereNull('deleted_at')->get();
        $users = DB::table('users')->whereNull('deleted_at')->get();
        $comments = DB::table('comments')->get();
        $views = DB::table('views')->get();
        $likes = DB::table('likes')->get();
        $topUser = User::withCount('articles')
        ->orderByDesc('articles_count')
        ->first();

        // $posts = DB::table('articles')->where('user_id')

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
        return to_route('register')->with('error','Only admin can create categories.');
    }

    public function show() {
        $categories = Category::withCount('articles')->get();

        return view('admin.categories', ['categories' => $categories]);
    }

    public function user(Request $request) {
        $search = $request->search;
        // $users = DB::table('users')->latest()->whereNull('deleted_at')->get();

        $users = User::where('name', 'LIKE', "%{$search}%")->latest()->get();

        return view('admin.users', ['users'=>$users]);
    }

    public function userRemove(User $user) {

        if (Auth::check() && Auth::user()->role === 'admin') {
            DB::table('views')->where('user_id',$user->id)->delete();
            DB::table('comments')->where('user_id',$user->id)->delete();
            DB::table('follows')->where('user_id',$user->id)->delete();
            DB::table('bookmarks')->where('user_id',$user->id)->delete();
            DB::table('likes')->where('user_id',$user->id)->delete();
            $user->delete();
            Article::where('user_id',$user->id)->delete();

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
            ->get();

        return view('admin.articles',['articles'=>$articles]);
    }

    public function destroy(Category $category) {
        if (Auth::check() && Auth::user()->role === 'admin') {
            $articles = DB::table('articles')->where('category_id', $category->id)->get(['id']);

            foreach ($articles as $article) {
                DB::table('likes')->where('article_id',$article->id)->delete();
                DB::table('comments')->where('article_id',$article->id)->delete();
                DB::table('views')->where('article_id',$article->id)->delete();
                DB::table('bookmarks')->where('article_id',$article->id)->delete();
            }
            DB::table('articles')->where('category_id',$category->id)->delete();
            DB::table('categories')->where('id',$category->id)->delete();
            return back()->with('success','Category deleted successfully');
        }
        return back()->with('error','Only admin can perform this task');
    }

    public function showarticle(Article $article) {
        // $article = Article::where('id',$article->id)->get();
        $comments = comments::where('article_id',$article->id)->whereNull('parent_id')->with('replies')->get();
        $replies = comments::where('article_id',$article->id)->whereNotNull('parent_id')->get();

        $likes = DB::table('likes')->where('article_id',$article)->get();

        return view('admin.article', ['article' => $article, 'comments' => $comments, 'replies' => $replies, 'likes' => $likes]);
    }
}
