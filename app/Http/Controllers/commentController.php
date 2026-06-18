<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Comment;
use App\Notifications\CommentNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    public function store(Request $request) {
        $request->validate([
            'article_id' => ['required','exists:articles,id'],
            'parent_id' => ['nullable','exists:comments,id'],
            'body'=> ['required','string','max:5000'],
        ]);

        $article = Article::findOrFail($request->article_id);

        if ($article->status !== 'published') {
            return back()->with('error', 'You cant comment on draft articles.');
        }

        $data = ([
            'user_id' => Auth::id(),
            'article_id' => $request->article_id,
            'parent_id' => $request->parent_id,
            'body' => $request['body'],
        ]);

        $comment = Comment::create($data);
        // $article->user->notify(new CommentNotification($comment));
        if ($comment->parent_id) {
            $parent = Comment::with('user')->find($comment->parent_id);

            if ($parent && $parent->user_id !== Auth::id()) {
                $parent->user->notify(new CommentNotification($comment));
            }
        }
        else {
            if ($article->user_id !== Auth::id()) {
                $article->user->notify(new CommentNotification($comment));
            }
        }
        return back()->with('success','comment posted successfully.');
    }
}
