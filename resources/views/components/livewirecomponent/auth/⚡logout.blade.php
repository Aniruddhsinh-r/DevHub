<?php

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

new class extends Component
{
    public function logout() {
        Auth::logout();
        // Auth::guard('web')->logout();
        session()->invalidate();
        session()->regenerateToken();

        return redirect('/')->with('success', 'Logged out successfully.');;
    }
};
?>

<div>
    <form wire:submit.prevent="logout" method="POST" class="w-full">
            @csrf
            <button type="submit" data-test="logout" class="w-full flex items-center gap-3 px-5 py-2 text-sm text-gray-700 font-medium hover:text-gray-500 transition">
                <svg class="w-5 h-5 text-gray-800" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
            </svg>Logout
        </button>
    </form>
</div>
