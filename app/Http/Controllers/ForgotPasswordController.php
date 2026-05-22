<?php

namespace App\Http\Controllers;

use App\Mail\passwordResetMail;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    public function create() {
        return view('auth.forgotpassword');
    }

    public function store(Request $request) {
        $request->validate(['email' => 'required|email|exists:users']);

        $to = $request->email;
        $otp = rand(1000,9999);
        $message = $otp;

        Cache::forget('otp');
        Cache::put('otp', $otp, now()->addMinutes(10));
        Mail::to($to)->queue(new passwordResetMail($message));

        return view('auth.otpVarification',['email' => $request->email]);
    }

    public function OTPform(){
        return view('auth.otpVarification');
    }

    public function OTPverify(Request $request) {
        $request->validate([
            'email' => 'required|email|exists:users',
            'otp' => 'required|digits:4'
        ]);

        $id = User::where('email', $request->email)->value('id');

        if($request->otp == Cache::get('otp')){
            Cache::forget('otp');
            return view('auth.resetPassword',["id"=>$id]);
        }
        return back()->with('error','Your provided otp is wrong');
    }

    public function resetform(){
        return view('auth.resetPassword');
    }

    public function reset(Request $request, $id){
        $request->validate([
            'password' => ['required', 'string', 'min:4', 'max:255'],
            'password_confirmation' => ['required', 'string', 'min:4', 'max:255'],
        ]);

        $pass = $request->input('password');
        $confirmpass = $request->input('password_confirmation');

        if ($pass === $confirmpass) {
            $user = User::where('id', $id)->first();
            $user->password = Hash::make($pass);
            $user->save();

            return to_route('home')->withInput()->with('success','your password has ben sucessfully updated.');
        }

        return back()->with('error',"fail to update password!");
    }
}
