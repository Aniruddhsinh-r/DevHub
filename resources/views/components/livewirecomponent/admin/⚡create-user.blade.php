<?php

use Livewire\Component;
use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Validation\Rule;
use App\Mail\InvitationMail;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Attributes\Sensitive;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Mail;

new #[Layout('layouts::dashboard')] class extends Component
{
    #[Validate]
    public $email = '';

    public function rules() {
        return [
            'email' => 'required|email|min:10|max:255|unique:users,email,',
        ];
    }

    public function sendInvite() {
        $to = $this->email;
        $message = URL::temporarySignedRoute(
            'invitation',
            now()->addMinutes(30),
            ['email' => $this->email]
        );
        Mail::to($to)->queue(new InvitationMail($message));
        
        $this->dispatch('live-notification', message: 'Invite sent successfully successfully.');
        // session()->flash('success', 'Invite sent successfully successfully.');
    }
};
?>

<div class="max-w-md mx-auto mt-16 bg-white rounded-xl shadow-sm border border-gray-200">
    <div class="p-8">
        <!-- Professional Header Section -->
        <div class="mb-6 border-b border-gray-100 pb-5">
            <h2 class="text-xl font-semibold text-gray-900 tracking-tight">
                Send User Invitation
            </h2>
            <p class="mt-1 text-sm text-gray-500">
                Generate a temporary registration token. The link sent will expire automatically after 30 minutes.
            </p>
        </div>

        <!-- Clean Status Notification -->
        @if (session()->has('message'))
            <div class="mb-5 p-3.5 text-sm text-emerald-800 bg-emerald-50 rounded-lg border border-emerald-200 flex items-center space-x-2.5" role="alert">
                <svg class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="font-medium">{{ session('message') }}</span>
            </div>
        @endif

        <form wire:submit.prevent="sendInvite" class="space-y-4">
            <div>
                <label for="email" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-2">
                    Email Address
                </label>
                <div class="relative rounded-md shadow-sm">
                    <input type="email" id="email" wire:model="email" placeholder="e.g., employee@company.com"
                        class="block w-full px-3.5 py-2.5 rounded-md border border-gray-300 text-sm font-normal text-gray-900 placeholder-gray-400 bg-white shadow-inner transition duration-150 ease-in-out focus:outline-none focus:ring-2 focus:ring-slate-500/20 focus:border-slate-600 {{ $errors->has('email') ? 'border-red-300 bg-red-50/20 focus:ring-red-500/10 focus:border-red-500' : '' }}" />
                </div>

                @error('email')
                    <p class="mt-2 text-xs text-red-600 font-medium flex items-center">
                        <span class="mr-1">🚨</span> {{ $message }}
                    </p>
                @enderror
            </div>

            <div class="pt-2">
                <button type="submit" wire:loading.attr="disabled" class="w-full h-[44px] flex items-center justify-center py-2.5 px-4 rounded-md shadow-sm text-sm font-medium text-white bg-slate-900 hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-900 transition duration-150 ease-in-out disabled:opacity-75 disabled:cursor-not-allowed">
                    <span wire:loading.remove wire:target="sendInvite">Issue Invitation Link</span>
                    <span wire:loading.flex wire:target="sendInvite" class="items-center justify-center space-x-2">
                        <svg class="animate-spin h-4 w-4 text-white shrink-0" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span class="text-white font-medium">Generating Token...</span>
                    </span>
                </button>
            </div>
        </form>
    </div>
</div>
