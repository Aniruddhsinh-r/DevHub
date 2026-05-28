<?php

namespace App\Http\Controllers;

use App\Models\Follow;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FollowerController extends Controller
{
    public function follow(User $id){
        if (Auth::check()) {
            Auth::user()->following()->toggle($id);
            return back();
        }
        return view('auth.register')->with('error','You must be authorize before following anyone.');
    }

    public function followers(User $user, Request $request) {
        $search = $request->follower;
        $followers = Follow::with('user')->where('followed_id', $user->id)->when($search, function ($query) use ($search) {
            $query->whereHas('user', function ($userQuery) use ($search) {
                $userQuery->where('name', 'LIKE', "%{$search}%");
            });
        })->latest()->paginate(10);

        return view('follow.followers', ['followers' => $followers,'user'=>$user]);
    }

    public function followings(User $user, Request $request) {
        $search = $request->following;
        $followings = Follow::with('followed')->where('follower_id', $user->id)->when($search, function ($query) use ($search) {
            $query->whereHas('followed', function ($userQuery) use ($search) {
                $userQuery->where('name', 'LIKE', "%{$search}%");
            });
        })->latest()->paginate(10);

        return view('follow.followings', ['followings' => $followings,'user'=>$user]);
    }
}
