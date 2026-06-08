<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    public function create(Request $request) {
        if (!Auth::check()) {
            return to_route('register.create')->with('error','Unauthorize action.');
        }
        $article = Article::findOrFail($request->article_id);

        if ($article->status !== 'published') {
            return back()->with('error', 'You cant comment on draft articles.');
        }

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

        Comment::create($data);
        return back()->with('success','comment posted successfully.');
    }
}
