<?php

use Livewire\Component;
use App\Models\User;
use Livewire\Attributes\On;

new class extends Component
{
    public $userId;
    public $email;

    #[On('trigger-recovery')]
    public function handleGlobalRecovery($id, $type)
    {
        if ($type === 'User') {
            $user = User::onlyTrashed()->find($id);
            if ($user) {
                $user->restore();
                
                // $this->dispatch('live-notification', message: 'User restored successfully!');
                session()->flash('success', 'User restored successfully!');
                $this->dispatch('user-restored', email: $user->email);
                // return redirect()->route('admin.invitations', ['email' => $user->email], navigate: true);
                return $this->redirectRoute('admin.invitations', navigate: true);
            }
        }
    }
};
?>

<div x-data="{ open: false, id: null, email: '', type: '' }"
     x-on:open-recovery.window="
        open = true;
        id = $event.detail.id;
        email = $event.detail.email;
        type = $event.detail.type;
     "
     x-on:user-restored.window="open = false"
     x-show="open"
     x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center">

    <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm" @click="open = false"></div>

    <div class="relative bg-white p-6 rounded-2xl shadow-2xl max-w-md w-full mx-4 z-10 border border-gray-100">
        <div class="flex items-center gap-3 text-amber-600 mb-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <h3 class="text-lg font-black text-gray-900 tracking-tight">Deactivated User Found</h3>
        </div>
        
        <p class="text-sm text-gray-600 leading-relaxed">
            The account for <span class="font-bold text-gray-900" x-text="email"></span> was previously deactivated by an administrator. Restoring this account will reactivate their existing account immediately, so no new invitation email is required.
        </p>

        <div class="mt-6 flex justify-end gap-3">
            <button type="button" @click="open = false" 
                class="px-4 py-2 border border-gray-200 rounded-xl text-sm font-semibold text-gray-700 hover:bg-gray-50 transition-colors">
                Cancel
            </button>

            <button type="button" dusk="RecoveryBTN" @click="$dispatch('trigger-recovery', { id: id, type: type, email: email })" wire:loading.attr="disabled"
                    class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-black transition-all shadow-md shadow-indigo-100">
                <span wire:loading.remove>Recover User</span>
                <span wire:loading>Processing...</span>
            </button>
        </div>
    </div>
</div>