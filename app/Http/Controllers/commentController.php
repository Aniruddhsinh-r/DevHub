<?php

namespace App\Http\Controllers;

use App\Models\comments;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class commentController extends Controller
{
    public function create(Request $request) {
        $request->validate([
            'article_id' => 'required|exists:articles,id',
            'body'=> 'required|string|max:5000',
        ]);

        // dd(['all' => $request]);

        $data = ([
            'user_id' => Auth::id(),
            'article_id' => $request->article_id,
            'body' => $request['body'],
        ]);

        if (Auth::check()) {
            comments::created($data);
            return back()->with('success','comment pested successfully.');
        }
    }
}
