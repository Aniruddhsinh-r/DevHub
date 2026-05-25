<?php

namespace App\Http\Controllers;

use App\Mail\RegistrationMail;
use App\Mail\WelcomeBackMail;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class RegisteredUserController extends Controller implements ShouldQueue
{
    public function create() {
        return view('auth.register');
    }

    public function store(Request $request) {
        $request->merge([
            'email' => strtolower($request->email),
        ]);
        $request->validate([
            'name' => ['required','min:5','max:50'],
            'email' => ['required', 'string', 'min:10', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:4', 'max:255'],
            'avtar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'bio' => ['nullable', 'max:2000', 'string'],
            'role' => ['required']
        ]);

        $avatarPath = null;
        if ($request->hasFile('avtar')) {
            $avatarPath = $request->file('avtar')->store('avatars', 'public');
        }

        $user = User::create([
            'name' => $request->name,
            'email' => strtolower($request->email),
            'password' => Hash::make($request->password),
            'bio' => $request->bio,
            'avtar'=> $avatarPath,
            'role' => $request->role,
        ]);

        Auth::login($user, $remember = true);

        $to = $request->email;
        $message = $user->name;

        Mail::to($to)->queue(new RegistrationMail($message));

        if ($request->role === 'admin') {
            return redirect('/adminpanel')->with('success','you register successfully.');
            // return view('admin.admin')->with('success','you register successfully.');
        }
        return to_route('home')->withInput()->with('success','User register successfully.');
    }
}
