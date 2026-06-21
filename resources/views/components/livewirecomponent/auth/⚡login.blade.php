<?php
namespace App\Livewire\Auth;

use Livewire\Component;
use App\Models\User;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

new class extends Component
{
    use WithFileUploads;

    public $email = '';
    public $password = '';
    public $remember = '';

    public function login() {
        $this->email = strtolower($this->email);

        $attempt = $this->validate([
            'email' => ['required', 'email', 'min:10', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'max:255'],
        ]);

        if (Auth::attempt($attempt, (bool) $this->remember)) {
            session()->regenerate();
            $admin = User::where('email', $this->email)->first()?->hasRole('admin');

            if ($admin) {
                session()->flash('success', 'Welcome back, admin!');
                return to_route('admin.dashboard');
            } else {
                $to = $this->email;
                $message = User::where('email',$this->email)->value('name');
                $subject = "Welcome back!";

                // Mail::to($to)->queue(new WelcomeBackMail($message, $subject));
                session()->flash('success', 'You have logged in successfully.');
                return redirect()->route('home');
            }
        }
        $this->dispatch('live-notification', message: 'The provided credentials do not match our records.');
    }
};
?>

<div>
    <div class="bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8" style="height: calc(100vh - 65px);">
        <div class="sm:mx-auto sm:w-full sm:max-w-md">
            <h2 class="mt-6 text-center text-3xl font-extrabold tracking-tight text-gray-900">
                Log in to your account
            </h2>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
            <div class="bg-white py-8 px-4 shadow-sm border border-gray-100 sm:rounded-2xl sm:px-10">
                <form wire:submit.prevent="login" class="space-y-5">
                    @csrf
                    <x-form.field name="email" type="email" label="Your email" placeholder="mailadd@gmail.com"></x-form.field>

                    <x-form.field name="password" type="password" label="Password" placeholder="••••••••"></x-form.field>
                    {{-- <div class="space-y-0.5">
                        <label for="password" class="block text-xs font-bold uppercase tracking-wider text-gray-700">Password</label>
                        <input type="password" id="password" name="password" class="border border-gray-400 w-full p-2 font-semibold text-sm text-gray-800 rounded-md shadow-xs focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="••••••••"/>
                    </div> --}}

                    <div class="flex items-center gap-2">
                        <input type="checkbox" id="remember" name="remember" wire:model="remember" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <label for="remember" class="text-sm text-gray-600">Remember me</label>
                    </div>

                    <div class="mt-8 flex items-center justify-between gap-4">
                        <a href="{{ route('password.forgot') }}" class="group inline-flex items-center gap-2 text-sm font-extrabold text-gray-500 hover:text-gray-800 transition-colors duration-150">
                            <span>Forgot password?</span>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-4 h-4 text-gray-400 group-hover:text-gray-700 group-hover:translate-x-1 transition-transform duration-150">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                            </svg>
                        </a>

                        <div>
                            <button type="submit" data-test="login-btn" class="inline-flex justify-center py-2.5 px-6 border border-transparent rounded-xl shadow-sm text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200">
                                <span wire:loading wire:target="register">Processing...</span>
                                <span wire:loading.remove wire:target="register">Log in</span>
                            </button>
                        </div>
                    </div>
                    <div class="text-center pt-1">
                        <p class="text-sm text-gray-500 font-medium">
                            Don't have an account?
                            <a href="{{ route('register.create') }}" class="font-bold text-indigo-600 hover:text-indigo-500 underline decoration-2 underline-offset-4 transition duration-150">
                                Create an account
                            </a>
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
