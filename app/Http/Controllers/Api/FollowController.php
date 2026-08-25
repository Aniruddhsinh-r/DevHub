<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class FollowController extends Controller
{
    public function store(string $uuid)
    {
        $user = User::where('uuid', $uuid)->first();

        if (! $user) {
            return response()->json(['message' => 'User not found.'], 404);
        }
        Gate::authorize('follow', $user);

        if ($user->id === Auth::id()) {
            return response()->json(['message' => 'You cannot follow yourself.'], 422);
        }

        $follow = Auth::user()->following()->where('followed_id', $user->id)->first();

        if ($follow) {
            return response()->json(['message' => 'You are already following this user.'], 409);
        }

        Auth::user()->following()->attach($user->id);

        return response()->json([
            'message' => 'User followed successfully.',
            'follow' => [
                'uuid' => $user->uuid,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ], 201);
    }

    public function destroy(string $uuid)
    {
        $user = User::where('uuid', $uuid)->first();

        if (! $user) {
            return response()->json(['message' => 'User not found.'], 404);
        }
        Gate::authorize('follow', $user);

        $deleted = Auth::user()->following()->detach($user->id);

        if (! $deleted) {
            return response()->json(['message' => 'You are not following this user.'], 404);
        }

        return response()->json([
            'message' => 'User unfollowed successfully.',
        ], 200);
    }
}
