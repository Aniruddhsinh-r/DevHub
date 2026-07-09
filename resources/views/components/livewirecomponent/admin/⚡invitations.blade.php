<?php

use Livewire\Component;
use App\Models\Invitation;
use App\Models\User;
use App\Events\UserCreate;
use Livewire\Attributes\On;
use App\Mail\InvitationMail;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Mail;

new #[Layout('layouts::dashboard')] class extends Component
{
    #[Validate]
    public $email = '';
    public $search = '';

    #[On('echo:users,UserCreate')]
    public function refresUsersList()
    {
        $this->dispatch('$refresh');
    }

    public function with() {
        Invitation::where('status', 'pending')->where('expires_at', '<', now())->update(['status' => 'expired']);
        return [
            'invitations' => Invitation::latest()->when($this->search, function ($query) {
                $query->where('email', 'like', '%' . $this->search . '%');
            })->get(),
        ];
    }

    public function mount() {
        $this->search = request()->query('search', '');
    }

    public function rules() {
        return [
            'email' => 'required|email|min:10|max:255',
        ];
    }

    public function sendInvite() {
        $this->validate();
        $email = strtolower($this->email);

        $deleted = User::onlyTrashed()->where('email',$email)->first();
        if($deleted) {
            $this->addError('email', 'This email is blocked.');
            $this->dispatch('open-recovery', 
                id: $deleted->id, 
                email: $deleted->email, 
                type: 'User'
            );
            return;
        }
        $activeUser = User::where('email', $email)->first();
        if ($activeUser) {
            $this->addError('email', 'This email is already registered to an active account.');
            return;
        }

        $exist = Invitation::where('email',$email)->first();
        if(!$exist) {
            Invitation::create([
                'email' => $email,
                'expires_at' => now()->addMinutes(30)
            ]);
        } elseif ($exist?->expires_at > now()) {
            $remaining = $exist->expires_at->diffForHumans(null, true);
            $this->dispatch('live-notification', message: "Please wait {$remaining} before resending.");
            return ;
        }

        $to = $email;
        $message = URL::temporarySignedRoute(
            'invitation',
            now()->addMinutes(30),
            ['email' => $email]
        );

        $this->email = '';
        Mail::to($to)->queue(new InvitationMail($message));
        UserCreate::dispatch();
        $this->dispatch('live-notification', message: 'Invite sent successfully.');
    }

    public function resend($id) {
        $invitation = Invitation::findOrFail($id);
        $exist = User::where('email',$invitation->email)->first();
        if($exist) {
            $this->dispatch('live-notification', message: "This email is already registered.");
            return;
        }

        if ($invitation->status === 'accepted') {
            $this->dispatch('live-notification', message: "Unable to send invitation.");
        } else if ($invitation->expires_at < now()) {
            $invitation->update([
                'created_at' => now(),
                'status' => 'pending',
                'expires_at' => now()->addMinutes(30)
            ]);

            $message = URL::temporarySignedRoute(
                'invitation',
                now()->addMinutes(30),
                ['email' => strtolower($invitation->email)]
            );

            Mail::to($invitation->email)->queue(new InvitationMail($message));
            UserCreate::dispatch();
            $this->dispatch('live-notification', message: 'Invitation resent successfully.');
        } else {
            $remaining = $invitation->expires_at->diffForHumans(null, true);
            $this->dispatch('live-notification', message: "Please wait {$remaining} before resending.");
        }
    }

    #[On('trigger-delete')]
    public function handleGlobalDelete($id, $type) {
        if ($type === 'Invitation') {
            $this->remove($id);
        }
    }

    public function remove($id) {
        Invitation::findOrFail($id)->delete();
        $this->dispatch('live-notification', message: 'Invitation removed successfully.');
        UserCreate::dispatch();
    }
};
?>

<div class="space-y-6">
    <div class="flex items-end justify-between">
        <div>
            <span class="text-[11px] font-black uppercase tracking-[0.25em] text-indigo-500">System Access</span>
            <h1 class="text-3xl font-black text-[#111827] mt-1 tracking-tight">Invitation Center</h1>
        </div>
        <div class="bg-white border border-gray-200 rounded-2xl px-4 py-2 shadow-md flex items-baseline gap-2">
            <span class="text-xs text-gray-400 font-bold uppercase tracking-wider">Pending Invites:</span>
            <span class="text-lg font-black text-[#111827]">{{ $invitations->where('status', 'pending')->count() }}</span>
        </div>
    </div>

    <div class="bg-white border border-gray-200 rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] overflow-hidden">
        <div class="p-6 bg-[#fafafa] border-b border-gray-100">
            <div class="flex flex-col md:flex-row md:items-center gap-6 md:gap-4 justify-between w-full">

                <form wire:submit="sendInvite" class="flex items-center gap-4 flex-grow max-w-2xl w-full">
                    <div class="relative flex-grow">
                        <input type="email" wire:model="email" placeholder="Enter colleague's email address..."
                            class="w-full h-12 rounded-2xl border border-gray-200 bg-white px-5 text-sm font-medium text-gray-900 placeholder-gray-400 focus:ring-4 focus:ring-indigo-100 focus:border-indigo-500 transition-all">
                        @error('email')
                            <span class="absolute -bottom-5 left-2 text-[10px] font-bold text-rose-500 uppercase">{{ $message }}</span>
                        @enderror
                    </div>
                    <button type="submit" class="h-12 px-6 rounded-2xl cursor-pointer bg-[#111827] hover:bg-black text-sm font-black text-white transition-all shadow-md shrink-0">
                        <span wire:loading.remove wire:target="sendInvite">Send Invite</span>
                        <span wire:loading.flex wire:target="sendInvite" class="items-center justify-center space-x-2">
                            <svg class="animate-spin h-4 w-4 text-white shrink-0" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span class="text-white font-medium"> Sending...</span>
                        </span>
                    </button>
                </form>

                <div class="relative w-full md:w-72 shrink-0">
                    <input type="text" wire:model.live="search" placeholder="Search articles..."
                        class="w-full h-12 rounded-2xl border border-gray-200 bg-[#fafafa] pl-4 pr-12 text-sm font-medium text-gray-700 outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition">
                    @if ($search)
                        <button type="button" wire:click="$set('search', '')" class="absolute right-11 top-1/2 -translate-y-1/2 w-5 h-5 rounded-full bg-gray-200 hover:bg-black hover:text-white text-gray-600 flex items-center justify-center transition-all duration-200">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    @endif
                    <span class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m1.85-5.15a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </span>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="text-[10px] uppercase tracking-[0.2em] text-gray-400 bg-gray-50/50">
                        <th class="px-8 py-4 font-black">Recipient</th>
                        <th class="px-8 py-4 font-black">Status</th>
                        <th class="px-8 py-4 font-black">Sent Date</th>
                        <th class="px-8 py-4 font-black text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse ($invitations as $invite)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-8 py-5 text-sm font-bold text-gray-900">{{ $invite->email }}</td>
                            <td class="px-8 py-5">
                                <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase
                                    {{ match($invite->status) {
                                        'accepted' => 'bg-emerald-50 text-emerald-600 border border-emerald-100',
                                        'expired'  => 'bg-rose-50 text-rose-600 border border-rose-100',
                                        'pending'  => 'bg-amber-50 text-amber-600 border border-amber-100',
                                    } }}">
                                    {{ $invite->status }}
                                </span>
                            </td>
                            <td class="px-8 py-5 text-sm text-gray-500 font-medium">{{ $invite->created_at->format('M d, Y') }}</td>
                            <td class="px-8 py-5 text-right space-x-2">
                                @if($invite->status !== 'accepted')
                                    <button wire:click="resend({{ $invite->id }})" class="text-[11px] font-bold text-indigo-600 hover:text-indigo-800 uppercase underline decoration-2 underline-offset-4">Resend</button>
                                @endif
                                <button class="text-[11px] font-bold text-rose-500 hover:text-rose-700 uppercase underline decoration-2 underline-offset-4"
                                     x-on:click="$dispatch('open-delete', { id: {{ $invite->id }}, title: '{{ addslashes($invite->email) }}', type: 'Invitation' })">Remove</button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-8 py-12 text-center text-sm text-gray-400 font-medium">No invitations found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
