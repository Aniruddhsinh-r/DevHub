<?php

namespace App\Http\Controllers;

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
            return back()->with('success','you successfully follow');
        }
        return to_route('register')->with('error','You must be authorize before following anyone.');
    }
}
