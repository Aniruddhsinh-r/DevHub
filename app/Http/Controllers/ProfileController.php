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
    public function show(User $user)
    {
        if (!$user || ($user->id === 1 && Auth::id() !== 1)) {
            return redirect()->back()->with('error', 'This author does not exist.');
        } 

        if ($user->id === Auth::id()) {
            return view('auth.myprofile', compact('user'));
        }

        if (Auth::check()) {
            $articles = Article::with(['user', 'category'])->where('user_id', $user->id)->latest()->get();
            return view('components.profile', compact('user','articles'));
        }
        return view('auth.register')->with('error','Unauthorize action');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        if(Auth::id() == $id){
            $user = Auth::user();
            return view('auth.editProfile',['user'=>$user]);
        }
        return back()->with('error','Unauthorize action.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $profile)
    {
        $request->validate([
            'name' => ['required','min:5','max:50'],
            'email' => ['required', 'string', 'min:10', 'max:255'],
            'password' => ['nullable', 'string', 'min:4', 'max:255', 'confirmed'],
            'password_confirmation' => ['nullable', 'string', 'min:4', 'max:255'],
            'avtar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'bio' => ['nullable', 'max:2000', 'string']
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'bio' => $request->bio,
        ];

        $user = $profile;

        if ($request->hasFile('avtar')) {
            if ($user->avtar) {
                Storage::disk('public')->delete($user->avtar);
            }
            $data['avtar'] = $request->file('avtar')->store('avtars','public');
        }

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        if ($user->update($data)) {
            return to_route('profile.index')->withInput()->with('success','your profile is sucessfully updated.');
        }
        return back()->with('error',"fail to update profile.");
    }
}
