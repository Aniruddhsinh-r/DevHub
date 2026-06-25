<?php

use Livewire\Component;
use App\Mail\PasswordResetMail;
use App\Models\User;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

new class extends Component
{
    #[Validate('required|email')]
    public $email = '';

    public function submit() {
        $this->validate();
        $user = User::where('email', $this->email)->first();

        $to = $this->email;
        $otp = random_int(100000,999999);
        $message = $otp;

        Cache::forget('otp_'. $to);
        Cache::put('otp_'. $to, $otp, now()->addMinutes(10));
        session(['otp_email' => $to]);
        Mail::to($to)->send(new PasswordResetMail($message));

        return $this->redirectRoute('password.forgot.otp', navigate: true);
    }
};
?>

<div>
    <div class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-md">
            <h2 class="mt-6 text-center text-3xl font-extrabold tracking-tight text-gray-900">
                Forgot your password?
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Enter your email address to reset a password.
            </p>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">

            @if (session('status'))
                <div class="mb-4 bg-green-50 border-l-4 border-green-500 p-4 rounded-r-md">
                    <p class="text-sm text-green-700">{{ session('status') }}</p>
                </div>
            @endif

            <div class="bg-white py-8 px-4 shadow-sm border border-gray-100 sm:rounded-2xl sm:px-10">
                <form wire:submit.prevent="submit" class="space-y-5">
                    @csrf
                    <div>
                        <div class="mt-1">
                            <x-form.field name="email" type="email" label="Your email" placeholder="mailadd@gmail.com"></x-form.field>
                        </div>
                        <div class="text-sm text-red-500 font-semibold py-1 rounded-md">
                            Note: If email matches with our records, an Otp has been sent.
                        </div>
                    </div>

                    <div>
                        <button type="submit" class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 transition-all">
                            <span wire:loading>Processing...</span>
                            <span wire:loading.remove wire:target="submit">Send Password Reset Link</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
