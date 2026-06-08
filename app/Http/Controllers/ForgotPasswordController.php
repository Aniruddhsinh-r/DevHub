<?php

namespace App\Http\Controllers;

use App\Mail\PasswordResetMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class ForgotPasswordController extends Controller
{
    public function create() {
        return view('auth.forgotpassword');
    }

    public function store(Request $request) {
        $request->validate(['email' => 'required|email|exists:users']);

        $to = $request->email;
        $otp = random_int(100000,999999);
        $message = $otp;

        Cache::forget('otp_'. $to);
        Cache::put('otp_'. $to, $otp, now()->addMinutes(10));
        session(['otp_email' => $to]);
        Mail::to($to)->send(new PasswordResetMail($message));

        return view('auth.otpVarification',['email' => $request->email]);
    }

    public function OTPform(){
        if (!session()->has('otp_email')) {
            return redirect()->route('password.forgot')->with('error', 'Please enter your email first.');
        }
        return view('auth.otpVarification');
    }

    public function OTPverify(Request $request) {
        $email = session('otp_email');
        $request->validate([
            'otp' => 'required|digits:6'
        ]);

        if($request->otp == Cache::get('otp_'. $email)){
            Cache::forget('otp_'. $email);
            session(['resetPass_email' => $email]);
            session()->forget('otp_email');

            return view('auth.resetPassword');
        }
        return back()->with('error','Your provided otp is wrong');
    }

    public function resetform(){
        if (!session()->has('resetPass_email')) {
            return redirect()->route('password.forgot')->with('error', 'Unauthorized access.');
        }
        return view('auth.resetPassword');
    }

    public function reset(Request $request){
        $email = session('resetPass_email');

        $request->validate([
          'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::where('email', $email)->firstOrFail();
        $user->password = Hash::make($request->password);
        $user->save();

        session()->forget('resetPass_email');

        return to_route('home')->with('success', 'Password updated successfully.');
    }
}
