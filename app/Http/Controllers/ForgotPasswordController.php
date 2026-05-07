<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    public function create() {
        return view('auth.forgotpassword');
    }

    public function store(Request $request) {
        $request->validate(['email' => 'required|email|exists:users']);

        return to_route('password.reset');
        // $status = Password::sendResetLink(
        //     $request->only('email')
        // );

        // return $status === Password::RESET_LINK_SENT
        //     ? back()->with(['status' => __($status)])
        //     : back()->withErrors(['email' => __($status)]);
    }

    public function resetform(){
        return view('auth.resetPassword');
    }

    public function reset(Request $request){
        $request->validate([
            'password' => ['required', 'string', 'min:4', 'max:255'],
            'password_confirmation' => ['required', 'string', 'min:4', 'max:255'],
        ]);

        $pass = $request->input('password');
        $confirmpass = $request->input('password_confirmation');

        if ($pass === $confirmpass) {
            $user = Auth::user();

            Auth::user()->password = Hash::make($pass);
            $user->save();

            return to_route('home')->withInput()->with('success','your password has ben sucessfully updated.');
        }

        return back()->with('error',"fail to update password!");
    }
}
