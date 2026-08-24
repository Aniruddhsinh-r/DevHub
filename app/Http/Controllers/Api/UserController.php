<?php

namespace App\Http\Controllers\Api;

use App\Enums\UserRole;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{

    public function profile(Request $request)
    {
        $user = Auth::user();

        return response()->json([
            'message' => 'Profile fetched successfully.',
            'user' => $user,
        ]);
    }

    public function update(Request $request) {
        $user = Auth::user();

        Gate::authorize('update', $user);

        $values = $request->validate([
            'name'     => ['sometimes', 'required', 'string', 'min:4', 'max:50'],
            'email'    => ['sometimes', 'required', 'email', 'min:10', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'bio'      => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'min:8', 'max:255', 'confirmed'],
            'avatar'   => ['nullable', 'image', 'max:5120'],
        ]);

        $data = collect($values)->only(['name', 'email', 'bio'])->toArray();

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        if (! empty($values['password'])) {
            $data['password'] = Hash::make($values['password']);
        }

        $user->update($data);

        return response()->json([
            'message' => 'Profile updated successfully.',
            'user'    => $user,
        ], 200);
    }

    public function delete(string $uuid)
    {
        $user = User::where('uuid', $uuid)->first();
        if (! $user) {
            return response()->json(['message' => 'record not found.'], 404);
        }

        Gate::authorize('delete', $user);

        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully.',
        ], 200);
    }

    public function forceDelete(string $uuid)
    {
        $user = User::withTrashed()->where('uuid', $uuid)->first();
        if (! $user) {
            return response()->json(['message' => 'record not found.'], 404);
        }

        Gate::authorize('forceDelete', $user);
        $user->forceDelete();

        return response()->json([
            'message' => 'User permanently deleted successfully.',
        ], 200);
    }

    public function index(Request $request)
    {
        $users = User::query()
            ->when($request->search, function ($q) use ($request) {
                $q->where(function ($query) use ($request) {
                    $query
                        ->where('name', 'like', "%{$request->search}%")
                        ->orWhere('email', 'like', "%{$request->search}%");
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 12));

        return response()->json([
            'users' => $users
        ], 200);
    }

    public function show(string $uuid)
    {
        $user = User::where('uuid',$uuid)->first();

        if (! $user) {
            return response()->json(['message' => 'record not found.'], 404);
        }

        Gate::authorize('view',$user);

        return response()->json([
            'user' => $user
        ]);
    }

    public function edit(Request $request, string $uuid) {
        $user = User::where('uuid', $uuid)->first();

        if (! $user) {
            return response()->json(['message' => 'record not found.'], 404);
        }

        Gate::authorize('update', $user);

        $values = $request->validate([
            'name'     => ['sometimes', 'required', 'string', 'min:4', 'max:50'],
            'email'    => ['sometimes', 'required', 'email', 'min:10', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'bio'      => ['nullable', 'string', 'max:255'],
            'avatar'   => ['nullable', 'image', 'max:5120'],
        ]);

        $data = collect($values)->only(['name', 'email', 'bio'])->toArray();

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->update($data);

        return response()->json([
            'message' => 'Profile updated successfully.',
            'user'    => $user,
        ], 200);
    }

}
