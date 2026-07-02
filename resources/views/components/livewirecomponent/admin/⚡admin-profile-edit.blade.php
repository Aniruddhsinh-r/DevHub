<?php

use Livewire\Component;
use App\Models\User;
use App\Events\UserCreate;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Validate;
use Livewire\Attributes\Sensitive;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

new #[Layout('layouts::dashboard')] class extends Component
{
    use WithFileUploads;
    public User $user;

    public $delete_avatar = false;

    public function mount() {
        $this->user = Auth::user();
        $this->name = $this->user->name;
        $this->email = $this->user->email;
        $this->bio = $this->user->bio;
    }

    #[Validate('required|min:5|max:50')]
    public $name;
    #[Validate('nullable|max:2000|string')]
    public $bio;
    #[Validate]
    public $email;
    #[Validate('nullable|string|min:8|max:255')]
    public $password_confirmation;
    #[Validate('nullable|image|mimes:jpeg,png,jpg,gif|max:2048')]
    public $avatar;
    #[Sensitive]
    #[Validate('nullable|string|min:8|max:255|confirmed')]
    public $password;

    public function rules() {
        return [
            'email' => 'required|email|min:10|max:255|unique:users,email,' . $this->user->id,
        ];
    }

    public function removeAvatar() {
        $this->avatar = null;
        $this->delete_avatar = true;
    }

    public function update() {
        $user = Auth::user();

        if (empty($this->password) && !empty($this->password_confirmation)) {
            $this->addError('password', 'The password field is required when confirming.');
            return;
        }

        $this->validate();
        $values['delete_avatar'] = $this->delete_avatar;

        $data = [
            'name' => $values['name'],
            'email' => $values['email'],
            'bio' => $values['bio']
        ];

        if ($this->delete_avatar) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $data['avatar'] = null;
        }
        if ($this->avatar && is_object($this->avatar)) {
            $data['avatar'] = $values['avatar']->store('avatars', 'public');
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
        }

        if (!empty($values['password'])) {
            $data['password'] = Hash::make($values['password']);
        }

        $updated = $user->update($data);
        if ($updated) {
            session()->flash('success', 'Your profile is successfully updated.');
            return $this->redirectRoute('admin.profile', navigate: true);
        }
        UserCreate::dispatch();
        $this->dispatch('live-notification', message: 'Failed to update profile.');
    }
};
?>

<div class="max-w-3xl mx-auto my-10 p-6 bg-white border border-gray-200 rounded-2xl shadow-sm">
    <h1 class="text-2xl font-bold text-gray-900 mb-2">My Profile</h1>
    <hr class="mb-6 border-gray-100">
    <form wire:submit.prevent="update" enctype="multipart/form-data">
        @csrf
        <div class="flex items-center gap-6 mb-6" x-data="{ imageUrl: '{{ $user->avatar ? asset('storage/' . $user->avatar) : 'https://ui-avatars.com/api/?name='.urlencode($user->name) }}' }">
            <div class="relative">
                <img :src="imageUrl" alt="Profile" class="w-24 h-24 rounded-full object-cover border-2 border-gray-100">
            </div>
            <div class="flex flex-col gap-2">
                <div class="flex items-center gap-3">
                    <input type="file" wire:model="avatar" id="avatar" x-ref="avatarInput" class="hidden" accept="image/*" @change="imageUrl = URL.createObjectURL($event.target.files[0])">
                    <div class="flex items-center gap-3">
                        <label for="avatar" class="cursor-pointer bg-black hover:bg-gray-800 text-white px-4 py-2 rounded-lg text-sm font-semibold transition flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>Change Image
                        </label>
                        @if(($user->avatar && !$delete_avatar) || $avatar)
                            <button type="button" wire:click="removeAvatar" @click="imageUrl = 'https://ui-avatars.com/api/?name={{ urlencode($user->name) }}'; $refs.avatarInput.value = '';" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition">
                                Remove
                            </button>
                        @endif
                    </div>
                </div>
                @error('avatar')
                    <p class="text-xs text-red-500 font-semibold mt-1">{{ $message }}</p>
                @else
                    <p class="text-xs text-gray-500">Only support PNG & JPEG under 2mb</p>
                @enderror
            </div>
        </div>
        <div class="grid grid-cols-1 gap-6 mb-10">
            <div>
                <x-form.field name="name" data-test="profile-name" type='text' label="Full name" placeholder="Enter name" :value="$user->name"></x-form.field>
            </div>
        </div>
        <h2 class="text-xl font-bold text-gray-900 mb-4">Account Security</h2>
        <hr class="mb-6 border-gray-100">
        <div class="space-y-6">
            <!-- Email Section -->
            <div class="flex flex-col md:flex-row md:items-end gap-4">
                <div class="flex-grow">
                    <x-form.field name="email" data-test="profile-gmail" type="email" label="Your email" placeholder="mailadd@gmail.com" :value="$user->email"></x-form.field>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <x-form.field name="password" data-test="profile-pass" type="password" label="New Password" placeholder="••••••••"></x-form.field>
                </div>
                <div>
                    <x-form.field name="password_confirmation" data-test="profil-conpass" type="password" label="Confirm Password" placeholder="••••••••"></x-form.field>
                </div>
            </div>
        </div>
        <div class="mt-10 flex justify-between items-center gap-3">
            <a href="{{ route('admin.profile') }}" wire:navigate class="group inline-flex items-center gap-2 text-sm font-extrabold text-gray-500 hover:text-gray-800 transition-colors duration-150">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-4 h-4 text-gray-400 group-hover:text-gray-700 group-hover:-translate-x-1 transition-transform duration-150">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
                <span>Back To Profile</span>
            </a>
            <div class="flex items-center gap-3">
                <button type="submit" data-test="update_profile" class="bg-black hover:bg-gray-900 text-white px-8 py-2.5 rounded-xl text-sm font-semibold transition shadow-lg shadow-gray-200">
                    <span wire:loading wire:target="update">Processing...</span>
                    <span wire:loading.remove wire:target="update">Update</span>
                </button>
            </div>
        </div>
    </form>
</div>
