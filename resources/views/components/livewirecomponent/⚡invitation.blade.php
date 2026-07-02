<?php

use Livewire\Component;
use App\Models\User;
use App\Enums\UserRole;
use App\Events\UserCreate;
use Illuminate\Http\Request;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

new class extends Component
{
    #[Validate('required|min:5|max:50')]
    public $name = '';
    #[Validate('required|email|min:10|max:255|unique:users,email,')]
    public $email = '';
    #[Validate('required|string|min:8|max:255')]
    public $password = '';
    public $originalEmail = '';

    public function mount(Request $request)
    {
        if (! $request->hasValidSignature()) {
            abort(401, 'This invitation link is invalid or has expired.');
        }

        $this->email = $request->route('email');
        $this->originalEmail = $request->route('email');
    
        if (User::where('email', $this->email)->exists()) {
            session()->flash('success', 'You already have an account with us! Try logging in.');
            return $this->redirectRoute('home', navigate: true);
        }
    }

    public function registerAccount(Request $request)
    {
        $this->validate();

        if ($this->email !== $this->originalEmail) {
            $this->email = $this->originalEmail;
            $this->addError('email', 'The invitation email cannot be modified.');
            return;
        }

        $user = User::create([
            'name' => $this->name,
            'email' => strtolower($this->email),
            'password' => Hash::make($this->password),
        ]);

        $user->assignRole(UserRole::AUTHOR);
        Auth::login($user, $remember = true);

        UserCreate::dispatch();
        session()->flash('success', 'Account created successfully.');
        return $this->redirectRoute('home', navigate: true);
    }
};
?>

<div class="max-w-md mx-auto my-8 bg-white rounded-xl shadow-sm border border-gray-200">
    <div class="p-8">

        <div class="mb-6 border-b border-gray-100 pb-5">
            <h2 class="text-xl font-semibold text-gray-900 tracking-tight">
                Complete Your Account Setup
            </h2>
            <p class="mt-1 text-sm text-gray-500">
                Provide your profile details below to finalize your registration workspace.
            </p>
        </div>

        <form wire:submit.prevent="registerAccount" class="space-y-5">

            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">
                    Invitation Assigned To
                </label>
                <div class="relative rounded-md shadow-sm">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                        <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                    </div>
                    <input type="email" wire:model="email" disabled
                        class="block w-full pl-10 px-3.5 py-2.5 rounded-md border border-gray-200 text-sm font-medium text-gray-400 bg-gray-50 cursor-not-allowed select-none focus:outline-none" />
                    </div>
                    @error('email') <p class="mt-1 text-xs text-red-600 font-medium">⚠️ {{ $message }}</p> @enderror
                <p class="mt-1.5 text-xs text-gray-400">This target address is verified and locked.</p>
            </div>

            <div>
                <label for="name" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-2">
                    Full Name
                </label>
                <input type="text" id="name" wire:model="name" placeholder="John Doe"
                    class="block w-full px-3.5 py-2.5 rounded-md border border-gray-300 text-sm font-normal text-gray-900 placeholder-gray-400 bg-white focus:outline-none focus:ring-2 focus:ring-slate-500/20 focus:border-slate-600 @error('name') border-red-300 @enderror" />
                @error('name') <p class="mt-1 text-xs text-red-600 font-medium">⚠️ {{ $message }}</p> @enderror
            </div>

            <div>
                <label for="password" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-2">
                    Choose Password
                </label>
                <input type="password" id="password" wire:model="password" placeholder="••••••••"
                    class="block w-full px-3.5 py-2.5 rounded-md border border-gray-300 text-sm font-normal text-gray-900 placeholder-gray-400 bg-white focus:outline-none focus:ring-2 focus:ring-slate-500/20 focus:border-slate-600 @error('password') border-red-300 @enderror" />
                @error('password') <p class="mt-1 text-xs text-red-600 font-medium">⚠️ {{ $message }}</p> @enderror
            </div>

            <div class="pt-3">
                <button type="submit"
                    class="w-full flex justify-center py-2.5 px-4 rounded-md shadow-sm text-sm font-medium text-white bg-slate-900 hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-900 transition duration-150 ease-in-out">
                    Save Account Settings
                </button>
            </div>

        </form>
    </div>
</div>
