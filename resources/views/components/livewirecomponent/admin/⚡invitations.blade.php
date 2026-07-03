<?php

use Livewire\Component;
use App\Models\Invitation;
use App\Mail\InvitationMail;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Mail;

new #[Layout('layouts::dashboard')] class extends Component
{
    public function with(): array
    {
        Invitation::where('status', 'pending')->where('expires_at', '<', now())->update(['status' => 'expired']);
        return [
            'invitations' => Invitation::latest()->get(),
        ];
    }

    #[Validate]
    public $email = '';

    public function rules() {
        return [
            'email' => 'required|email|min:10|max:255|unique:users,email,',
        ];
    }

    public function sendInvite() {
        $this->validate();
        $exist = Invitation::where('email',$this->email)->exists();
        if(!$exist) {
            Invitation::create([
                'email' => strtolower($this->email),
                'expired_at' => now()->addMinutes(30)
            ]);
        }
        
        $to = strtolower($this->email);
        $message = URL::temporarySignedRoute(
            'invitation',
            now()->addMinutes(30),
            ['email' => strtolower($this->email)]
        );

        $this->email = '';
        Mail::to($to)->queue(new InvitationMail($message));
        $this->dispatch('live-notification', message: 'Invite sent successfully successfully.');
    }

    public function resend(int $id): void
    {
        $invitation = Invitation::findOrFail($id);

        if ($invitation->created_at->addMinutes(30)->isPast()) {
            $invitation->update([
                'created_at' => now(), 
                'status' => 'pending'
            ]);

            $message = URL::temporarySignedRoute(
                'invitation',
                now()->addMinutes(30),
                ['email' => strtolower($invitation->email)]
            );
            Mail::to($invitation->email)->queue(new InvitationMail($message));
            $this->dispatch('live-notification', message: 'Invitation resent successfully.');
        } else {
            $remaining = $invitation->created_at->addMinutes(30)->diffForHumans();
            $this->dispatch('live-notification', message: "Please wait {$remaining} before resending.");
        }
    }

    public function remove(int $id): void
    {
        Invitation::findOrFail($id)->delete();
        $this->dispatch('live-notification', message: 'Invitation removed successfully.');
    }
};
?>

<div class="space-y-6">
    <div class="flex items-end justify-between">
        <div>
            <span class="text-[11px] font-black uppercase tracking-[0.25em] text-indigo-500">System Access</span>
            <h1 class="text-3xl font-black text-[#111827] mt-1 tracking-tight">Invitation Center</h1>
        </div>
        <div class="bg-indigo-50 border border-indigo-100 rounded-2xl px-5 py-3 text-right">
            <span class="text-[10px] font-bold text-indigo-600 uppercase tracking-widest block">Pending Invites</span>
            <span class="text-2xl font-black text-indigo-900">{{ $invitations->where('status', 'pending')->count() }}</span>
        </div>
    </div>

    <div class="bg-white border border-gray-200 rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] overflow-hidden">
        
        <div class="p-6 bg-[#fafafa] border-b border-gray-100">
            <form wire:submit="sendInvite" class="flex items-center gap-4">
                <div class="relative flex-grow">
                    <input type="email" wire:model="email" placeholder="Enter colleague's email address..." 
                        class="w-full h-12 rounded-2xl border border-gray-200 bg-white px-5 text-sm font-medium text-gray-900 placeholder-gray-400 focus:ring-4 focus:ring-indigo-100 focus:border-indigo-500 transition-all">
                    @error('email') <span class="absolute -bottom-5 left-2 text-[10px] font-bold text-rose-500 uppercase">{{ $message }}</span> @enderror
                </div>
                <button type="submit" class="h-12 px-6 rounded-2xl bg-[#111827] hover:bg-black text-sm font-black text-white transition-all shadow-md">
                    Send Invite
                </button>
            </form>
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
                                    {{ $invite->status === 'pending' ? 'bg-amber-50 text-amber-600' : 'bg-emerald-50 text-emerald-600' }}">
                                    {{ $invite->status }}
                                </span>
                            </td>
                            <td class="px-8 py-5 text-sm text-gray-500 font-medium">{{ $invite->created_at->format('M d, Y') }}</td>
                            <td class="px-8 py-5 text-right space-x-2">
                                @if($invite->status === 'pending')
                                    <button wire:click="resend({{ $invite->id }})" class="text-[11px] font-bold text-indigo-600 hover:text-indigo-800 uppercase underline decoration-2 underline-offset-4">Resend</button>
                                @endif
                                <button wire:click="remove({{ $invite->id }})" class="text-[11px] font-bold text-rose-500 hover:text-rose-700 uppercase underline decoration-2 underline-offset-4">Remove</button>
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