<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function index() {
        $articles = DB::table('articles')->get();
        $users = DB::table('users')->get();
        $comments = DB::table('comments')->get();
        $views = DB::table('views')->get();

        return view('admin.dashboard',['articles' => $articles,'users' => $users,'comments' => $comments,'views'=>$views]);
    }
}
