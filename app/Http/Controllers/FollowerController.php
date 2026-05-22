<?php

namespace App\Http\Controllers;

use App\Models\follows;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FollowerController extends Controller
{
    public function follow(User $id){
        if (Auth::check()) {
            // DB::transaction(function () use ($id) {$this->user->});
            Auth::user()->following()->toggle($id);
            return back();
        }
        return to_route('register')->with('error','You must be authorize before following anyone.');
    }

    public function followers(User $user, Request $request) {
        $search = $request->follower;
        $followers = follows::with('user')->where('followed_id', $user->id)->when($search, function ($query) use ($search) {
            $query->whereHas('user', function ($userQuery) use ($search) {
                $userQuery->where('name', 'LIKE', "%{$search}%");
            });
        })->latest()->paginate(10);

        return view('follow.followers', ['followers' => $followers]);
    }

    public function followings(User $user, Request $request) {
        $search = $request->following;
        $followings = follows::with('user')->where('follower_id', $user->id)->when($search, function ($query) use ($search) {
            $query->whereHas('user', function ($userQuery) use ($search) {
                $userQuery->where('name', 'LIKE', "%{$search}%");
            });
        })->latest()->paginate(10);

        return view('follow.followings', ['followings' => $followings]);
    }
}
