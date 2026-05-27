<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\follows;
use App\Models\User;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function articleSearch(Request $request) {
        $search = $request->search;
        $articles = Article::with(['user', 'category'])
        ->where('status', 'published')
        ->where(function($query) use ($search) {
            $query->where('title', 'LIKE', "%{$search}%")
                  ->orWhere('excerpt', 'LIKE', "%{$search}%")
                  ->orWhere('body', 'LIKE', "%{$search}%");
        })->latest()->paginate(9);

        if (Auth()->user()?->role === 'admin') {
            return view('admin.articles',['articles'=>$articles]);
        } else {
            return view('articles.article',['articles'=>$articles]);
        }
    }

    public function followingSearch(Request $request){
        $search = $request->following;
    }
}
