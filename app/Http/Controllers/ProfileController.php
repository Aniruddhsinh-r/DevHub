<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();

        return view('auth.myprofile', compact('user'));
    }

    /**
     * Display the specified resource.
     */
    public function show(User $profile)
    {
        $user = $profile;

        if (!$user || ($user->hasRole('admin') && !Auth::user()->hasRole('admin'))) {
            return redirect()->back()->with('error', 'This author does not exist.');
        }

        if ($user->id === Auth::id()) {
            return view('auth.myprofile', compact('user'));
        }

        $articles = $user->articles()->with(['category'])->latest()->get();
        
        return view('components.profile', compact('user','articles'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit()
    {
        $user = Auth::user();
        return view('auth.editProfile',['user'=>$user]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => ['required','min:5','max:50'],
            'email' => ['required', 'string', 'min:10', 'max:255' ,Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8', 'max:255', 'confirmed'],
            'password_confirmation' => ['nullable', 'string', 'min:8', 'max:255'],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'bio' => ['nullable', 'max:2000', 'string']
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'bio' => $request->bio,
        ];

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $data['avatar'] = $request->file('avatar')->store('avatars','public');
        }

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        if ($user->update($data)) {
            return to_route('profile.index')->with('success','your profile is successfully updated.');
        }
        return back()->with('error',"fail to update profile.");
    }
}
