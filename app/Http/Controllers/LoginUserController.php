<?php

namespace App\Http\Controllers;

use App\Mail\WelcomeBackMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class LoginUserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function create()
    {
        return view('auth.login');
    }

    public function store(Request $request)
    {
        $request->merge([
            'email' => strtolower($request->email),
        ]);

        $attempt = $request->validate([
            'email' => ['required', 'email', 'min:10', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'max:255'],
        ]);

        if (Auth::attempt($attempt, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $admin = User::where('email', $request->email)->whereRole('admin')->exists();

            if ($admin) {
                return to_route('admin.dashboard')->with('success', 'Welcome back, admin!');
            } else {
                $to = $request->email;
                $message = User::where('email',$request->email)->value('name');
                $subject = "Welcome back!";

                // Mail::to($to)->queue(new WelcomeBackMail($message, $subject));
                return to_route('home')->with('success', 'You have logged in successfully.');
            }
        }
        return back()->withInput()->with('error','The provided credentials do not match our records.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy()
    {
        Auth::logout();

        return to_route('home')->with('success','Logout successfully!');
    }
}
