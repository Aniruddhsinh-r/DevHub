<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Like;
use App\Models\User;
use App\Models\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    use AuthorizesRequests;

    // public function index() {
    //     $articleCount = Article::where('status', 'published')->count();
    //     $articles = Article::where('status', 'published')->latest()->take(4)->get();
    //     $users = User::count();
    //     $comments = Comment::count();
    //     $views = View::count();
    //     $likes = Like::count();
    //     $topUser = User::withCount('articles')->orderByDesc('articles_count')->first();

    //     return view('admin.dashboard',['articles' => $articles,'users' => $users,'comments' => $comments,'views'=>$views,'likes'=>$likes,'topUser'=>$topUser,'articleCount'=>$articleCount]);
    // }

    // public function create(Request $request) {
    //     $this->authorize('create', Category::class);

    //     $request->validate([
    //         'name' => ['required','min:3','max:20','string','unique:categories,name']
    //     ]);

    //     $name = strip_tags($request->name);
    //     $slug = Str::slug($name, '-');

    //     if (Category::where('slug', $slug)->exists()) {
    //         return back()->withErrors(['name' => 'This category name generates a duplicate entry.'])->withInput();
    //     }

    //     Category::create([
    //         'name' => $name,
    //         'slug' => $slug,
    //         'created_at' => now(),
    //     ]);
    //     return back()->with('success','Category created successfully.');
    // }

    // public function showUser(User $user) {
    //     $articles = $user->articles()->with(['category'])->latest()->get();

    //     return view('admin.users.userProfile', ['user'=>$user,'articles'=>$articles]);
    // }

    public function userArticle(User $user) {
        $articles = $user->articles()->with(['category'])->latest()->get();
        return view('admin.users.userPublished', ['articles'=>$articles]);
    }

    // public function show() {
    //     $categories = Category::withCount('articles')->latest()->paginate(6);
    //     return view('admin.categories', ['categories' => $categories]);
    // }

    // public function user(Request $request) {
    //     $search = $request->search;
    //     $users = User::role('author')->where('name', 'LIKE', "%{$search}%")->paginate(6);

    //     return view('admin.users.users', ['users'=>$users]);
    // }

    // public function userRemove(User $user) {
    //     $this->authorize('remove', User::class);

    //     DB::transaction(function () use ($user) {
    //         $user->views()->delete();
    //         $user->comments()->delete();
    //         $user->bookmarks()->delete();
    //         $user->likes()->delete();
    //         $user->articles()->delete();
    //         $user->notifications()->delete();
    //         $user->delete();
    //     });
    //     return back()->with('success','User removed successfully.');
    // }

    // public function articles(Request $request) {
    //     $search = $request->search;
    //     $articles = Article::query()->where('status', 'published')
    //         ->when($search, function ($query, $search) {
    //             $query->where(function ($query) use ($search) {
    //                 $query->where('title', 'like', "%{$search}%")
    //                       ->orWhere('excerpt', 'like', "%{$search}%")
    //                       ->orWhere('body', 'like', "%{$search}%");
    //             });
    //         })
    //         ->latest()
    //         ->paginate(9);

    //     return view('admin.articles.articles',['articles'=>$articles]);
    // }

    // public function destroy(Category $category) {
    //     $this->authorize('delete', Category::class);
    //     $category->delete();

    //     return back()->with('success', 'Category deleted successfully.');
    // }

    // public function showArticle(Article $article) {
    //     $comments = $article->comments()->whereNull('parent_id')->with('replies')->get();
    //     $likes = $article->likes()->get();

    //     return view('admin.articles.article', ['article' => $article, 'comments' => $comments, 'likes' => $likes]);
    // }
}
