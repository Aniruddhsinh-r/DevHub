<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Like;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LikeController extends Controller
{
    public function like(Article $article)
    {
        if (!Auth::check()) {
            return back()->with('error', 'You must be logged in to like this article.');
        }
        $like = Like::where(['article_id' => $article->id ,'user_id' => Auth::id()]);

        if ($like->exists()) {
            $like->delete();
            return back()->with('success','Article Unlike');
        } else {
            Like::create(['user_id' => Auth::id(),
            'article_id' => $article->id,
            'created_at' => now()]);
        }
        return back()->with('success','like successfully');
    }
}
