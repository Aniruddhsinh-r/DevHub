<?php

namespace App\Http\Controllers;

use App\Models\Follow;
use App\Models\User;
use App\Notifications\NewFollowerNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FollowerController extends Controller
{
    public function follow(User $id){
        if(Auth::id() !== $id->id) {
            Auth::user()->following()->toggle($id);
            $alreadyFollowing = Auth::user()->following()->where('followed_id', $id->id)->exists();

            if ($alreadyFollowing) {
                $id->notify(new NewFollowerNotification(Auth::user()));
            }
            return back();
        }
        return back()->with('error','You cant follow yourself.');
    }

    public function followers(User $user, Request $request) {
        $search = $request->follower;
        $followers = Follow::with('follower')->where('followed_id', $user->id)->when($search, function ($query) use ($search) {
            $query->whereHas('follower', function ($userQuery) use ($search) {
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
