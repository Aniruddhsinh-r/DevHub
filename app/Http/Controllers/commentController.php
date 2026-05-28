<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\comments;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    public function create(Request $request) {
        // dd($request->all());

        $request->validate([
            'article_id' => ['required','exists:articles,id'],
            'parent_id' => ['nullable','exists:comments,id'],
            'body'=> ['required','string','max:5000'],
        ]);

        $data = ([
            'user_id' => Auth::id(),
            'article_id' => $request->article_id,
            'parent_id' => $request->parent_id,
            'body' => $request['body'],
        ]);

        // $article = Article::where('id',$request->article_id)->get();

        if (Auth::check()) {
            comments::create($data);
            return back()->with('success','comment posted successfully.');
        }
    }
}
