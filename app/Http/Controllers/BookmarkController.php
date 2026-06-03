<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Bookmark;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class BookmarkController extends Controller
{
    public function bookmark(Article $article) {
        if (!Auth::check()) {
            return back()->with('error', 'You must be logged in to bookmark this article.');
        }

        if ($article->status !== 'published') {
            return back()->with('error', 'You cant bookmark draft articles.');
        }

        $bookmark = Bookmark::where(['article_id' => $article->id ,'user_id' => Auth::id()]);

        if($bookmark->exists()) {
            $bookmark->delete();
            return back()->with('success','remove from bookmark');
        } else {
            Bookmark::create(['user_id'=>Auth::id(),
            'article_id'=>$article->id,
            'created_at'=>now()]);
        }
        return back()->with('success','article bookmark');
    }

    public function show(User $user) {
        if (Auth::user()->role !== 'author') {
            return back()->with('error', 'You must be login as Author to see your bookmark article.');
        }

        $articles = Article::whereHas('bookmarks', function ($query) {
            $query->where('user_id', Auth::id());
        })
        ->with(['user', 'category'])
        ->latest()
        ->get();

        return view('bookmarks.bookmarkArticle', ['articles' => $articles]);
    }
}
