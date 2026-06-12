<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Like;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LikeController extends Controller
{
    public function like(Article $article)
    {
        if ($article->status !== 'published') {
            return back()->with('error', 'You cant like draft articles.');
        }

        $like = Like::where(['article_id' => $article->id, 'user_id' => Auth::id()])->first();
        if ($like) {
            $like->delete();
            return back()->with('success', 'Article unliked.');
        }

        Like::create(['user_id' => Auth::id(), 'article_id' => $article->id]);
        return back()->with('success', 'Article liked.');
    }
}
