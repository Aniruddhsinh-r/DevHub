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
        $articles = Article::where('title', 'LIKE', "%{$search}%")->orWhere('excerpt', 'LIKE', "%{$search}%")->orWhere('body', 'LIKE', "%{$search}%")->get();

        if (Auth()->user()->role === 'author') {
            return view('articles.article',['articles'=>$articles]);
        } else {
            return view('admin.articles',['articles'=>$articles]);
        }
    }

    public function followingSearch(Request $request){
        $search = $request->following;
    }
}
