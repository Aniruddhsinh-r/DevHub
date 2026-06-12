<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Bookmark;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class BookmarkController extends Controller
{
    public function bookmark(Article $article) {
        if ($article->status !== 'published') {
            return back()->with('error', 'You cant bookmark draft articles.');
        }

        if($article->isBookmarkedByMe()) {
            $article->bookmarks()->detach(auth()->id());
            return back()->with('success','remove from bookmark');
        } else {
            Bookmark::create(['user_id'=>Auth::id(),
            'article_id'=>$article->id,
            'created_at'=>now()]);
        }
        return back()->with('success','article bookmark');
    }

    public function show() {
        $articles = Auth::user()->bookmarkedArticles()->with(['user', 'category'])->latest()->get();

        return view('bookmarks.bookmarkArticle', ['articles' => $articles]);
    }
}
