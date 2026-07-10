<?php
namespace App\Livewire\Auth;

use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Validate;
use App\Enums\UserRole;
use Livewire\Attributes\Sensitive;

new class extends Component
{
    #[Validate('required|email|min:10|max:255')]
    public $email = '';
    public $remember = '';
    #[Sensitive]
    #[Validate('required|string|min:8|max:255')]
    public $password = '';

    public function login() {
        $this->email = strtolower($this->email);

        $attempt = $this->validate();

        if (Auth::attempt($attempt, (bool) $this->remember)) {
            session()->regenerate();
            $admin = User::where('email', $this->email)->first()?->hasRole(UserRole::ADMIN);

            if ($admin) {
                session()->flash('success', 'Welcome back, admin!');
                return to_route('admin.dashboard');
            } else {
                $to = $this->email;
                $message = User::where('email',$this->email)->value('name');
                $subject = "Welcome back!";

                // Mail::to($to)->queue(new WelcomeBackMail($message, $subject));
                session()->flash('success', 'You have logged in successfully.');
                return $this->redirectRoute('home', navigate: true);
            }
        }
        $this->dispatch('live-notification', message: 'The provided credentials do not match our records.');
    }
};
?>

<div>
    <div class="min-h-screen bg-gray-100 flex items-center justify-center px-4 py-12 sm:px-6 lg:px-8" style="min-height: calc(100vh - 65px);">
        <div class="w-full max-w-4xl grid grid-cols-1 lg:grid-cols-5 rounded-3xl overflow-hidden shadow-xl shadow-gray-300/40 bg-white ring-1 ring-gray-900/5">

            {{-- Left: decorative panel describing real site capabilities --}}
            <div class="hidden lg:flex lg:col-span-2 flex-col justify-between bg-gray-900 px-10 py-12 relative overflow-hidden">
                <div class="absolute -top-24 -right-20 h-64 w-64 rounded-full bg-gray-700/30 blur-3xl"></div>
                <div class="absolute -bottom-24 -left-16 h-64 w-64 rounded-full bg-gray-700/20 blur-3xl"></div>

                <div class="relative flex items-center gap-2.5">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-white/10 ring-1 ring-white/15">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" class="h-4.5 w-4.5 text-white">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 5.5A1.5 1.5 0 015.5 4H13l5 5v9.5a1.5 1.5 0 01-1.5 1.5h-11A1.5 1.5 0 014 18.5v-13z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 4v4.5a1 1 0 001 1H18M7.5 12.5h6M7.5 15.5h6M7.5 9.5h2" />
                        </svg>
                    </span>
                    <span class="text-lg font-bold tracking-tight text-white">DevHub</span>
                </div>

                <div class="relative space-y-5 my-10">
                    <p class="text-xl font-bold leading-snug tracking-tight text-white">
                        One place to write, read, and follow it all.
                    </p>

                    <div class="space-y-3">
                        <div class="flex items-center gap-3 rounded-xl bg-white/[0.06] ring-1 ring-white/10 px-3.5 py-3">
                            <span class="flex h-8 w-8 flex-none items-center justify-center rounded-lg bg-white/10">
                                <svg class="h-4 w-4 text-white" viewBox="0 0 20 20" fill="currentColor"><path d="M13 7H7v6h6V7z" /><path fill-rule="evenodd" d="M7 2a1 1 0 012 0v1h2V2a1 1 0 112 0v1h2a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h2V2zM5 5v10h10V5H5z" clip-rule="evenodd" /></svg>
                            </span>
                            <p class="text-sm font-medium text-white/80">Publish your own articles</p>
                        </div>

                        <div class="flex items-center gap-3 rounded-xl bg-white/[0.06] ring-1 ring-white/10 px-3.5 py-3">
                            <span class="flex h-8 w-8 flex-none items-center justify-center rounded-lg bg-white/10">
                                <svg class="h-4 w-4 text-white" viewBox="0 0 20 20" fill="currentColor"><path d="M10 9a3 3 0 100-6 3 3 0 000 6zM6 8a2 2 0 11-4 0 2 2 0 014 0zM1.49 15.326a.78.78 0 01-.358-.442 3 3 0 014.308-3.516 6.484 6.484 0 00-1.905 3.959c-.023.222-.014.442.025.654a4.97 4.97 0 01-2.07-.655zM16.44 15.98a4.97 4.97 0 002.07-.654.78.78 0 00.357-.442 3 3 0 00-4.308-3.517 6.484 6.484 0 011.907 3.96 2.32 2.32 0 01-.026.654zM18 8a2 2 0 11-4 0 2 2 0 014 0zM5.304 16.19a.844.844 0 01-.277-.71 5 5 0 019.947 0 .843.843 0 01-.277.71A6.975 6.975 0 0110 18a6.974 6.974 0 01-4.696-1.81z" /></svg>
                            </span>
                            <p class="text-sm font-medium text-white/80">Follow the writers you like</p>
                        </div>

                        <div class="flex items-center gap-3 rounded-xl bg-white/[0.06] ring-1 ring-white/10 px-3.5 py-3">
                            <span class="flex h-8 w-8 flex-none items-center justify-center rounded-lg bg-white/10">
                                <svg class="h-4 w-4 text-white" viewBox="0 0 20 20" fill="currentColor"><path d="M5 3a1 1 0 00-1 1v15l6-4 6 4V4a1 1 0 00-1-1H5z" /></svg>
                            </span>
                            <p class="text-sm font-medium text-white/80">Bookmark articles to read later</p>
                        </div>

                        <div class="flex items-center gap-3 rounded-xl bg-white/[0.06] ring-1 ring-white/10 px-3.5 py-3">
                            <span class="flex h-8 w-8 flex-none items-center justify-center rounded-lg bg-white/10">
                                <svg class="h-4 w-4 text-white" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10c0 3.31-3.58 6-8 6a9 9 0 01-2.44-.33L3 17l1.06-3.18C2.79 12.6 2 11.36 2 10c0-3.31 3.58-6 8-6s8 2.69 8 6z" clip-rule="evenodd" /></svg>
                            </span>
                            <p class="text-sm font-medium text-white/80">Comment and join the discussion</p>
                        </div>
                    </div>
                </div>

                <p class="relative text-xs text-white/40">
                    Your account, your articles, your feed.
                </p>
            </div>

            {{-- Right: form --}}
            <div class="lg:col-span-3 px-6 py-10 sm:px-10 lg:px-14 flex flex-col justify-center">
                <div class="mx-auto w-full max-w-sm">

                    <div class="flex items-center gap-2.5 mb-8 lg:hidden">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-gray-900 text-white">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" class="h-4.5 w-4.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 5.5A1.5 1.5 0 015.5 4H13l5 5v9.5a1.5 1.5 0 01-1.5 1.5h-11A1.5 1.5 0 014 18.5v-13z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 4v4.5a1 1 0 001 1H18M7.5 12.5h6M7.5 15.5h6M7.5 9.5h2" />
                            </svg>
                        </span>
                        <span class="text-lg font-bold tracking-tight text-gray-900">DevHub</span>
                    </div>

                    <h2 class="text-2xl font-bold tracking-tight text-gray-900">
                        Welcome back
                    </h2>
                    <p class="mt-1.5 text-sm text-gray-500">
                        Log in to keep reading, publishing, and following the writers you like.
                    </p>

                    <form wire:submit.prevent="login" class="mt-8 space-y-5">
                        @csrf

                        <x-form.field name="email" autocomplete="email" type="email" label="Your email" placeholder="mailadd@gmail.com"></x-form.field>

                        <x-form.field name="password" type="password" label="Password" placeholder="••••••••"></x-form.field>

                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <input type="checkbox" id="remember" name="remember" wire:model="remember" class="h-4 w-4 rounded border-gray-300 text-gray-900 focus:ring-gray-900">
                                <label for="remember" class="text-sm text-gray-600">Remember me</label>
                            </div>

                            <a href="{{ route('password.forgot') }}" wire:navigate class="text-sm font-semibold text-gray-500 hover:text-gray-900 transition-colors duration-150">
                                Forgot password?
                            </a>
                        </div>

                        <div class="pt-2">
                            <button
                                type="submit"
                                data-test="login-btn"
                                class="inline-flex w-full cursor-pointer items-center justify-center gap-2 rounded-xl bg-gray-900 px-6 py-3 text-sm font-semibold text-white shadow-sm transition-all duration-200 hover:bg-black focus:outline-none focus:ring-2 focus:ring-gray-900 focus:ring-offset-2"
                            >
                                <svg wire:loading class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                </svg>
                                <span wire:loading>Processing...</span>
                                <span wire:loading.remove wire:target="login">Log in</span>
                            </button>
                        </div>
                    </form>

                    <p class="mt-6 text-center text-sm text-gray-500 font-medium">
                        Don't have an account?
                        <a href="{{ route('register.create') }}" wire:navigate class="font-bold text-gray-900 hover:underline underline-offset-4 decoration-2 transition duration-150">
                            Create an account
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>