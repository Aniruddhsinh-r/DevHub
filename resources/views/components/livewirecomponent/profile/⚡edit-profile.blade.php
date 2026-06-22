<?php

use Livewire\Component;
use App\Models\User;
use Livewire\WithFileUploads;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Sensitive;

new class extends Component
{
    use WithFileUploads;
    public User $user;

    public function mount() {
        $this->user = Auth::user();

        $this->name = $this->user->name;
        $this->email = $this->user->email;
        $this->bio = $this->user->bio;
    }

    public $name;
    public $email;
    public $password_confirmation;
    public $avatar;
    public $bio;
    #[Sensitive]
    public $password;

    public function update() {
        $user = Auth::user();

        $this->validate([
            'name' => ['required','min:5','max:50'],
            'email' => ['required', 'string', 'min:10', 'max:255' ,Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8', 'max:255', 'confirmed'],
            'password_confirmation' => ['nullable', 'string', 'min:8', 'max:255'],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:1024', 'dimensions:max_width=1000,max_height=1000'],
            'bio' => ['nullable', 'max:2000', 'string']
        ]);

        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'bio' => $this->bio,
        ];

        if ($this->avatar) {
            $data['avatar'] = $this->avatar->store('avatars', 'public');
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
        }

        if ($this->password) {
            $data['password'] = Hash::make($this->password);
        }

        if ($user->update($data)) {
            session()->flash('success','your profile is successfully updated.');
            return to_route('profile.index');
        }
        // return back()->with('error',"fail to update profile.");
        $this->dispatch('live-notification', message: 'fail to update profile.');
    }
};
?>

<div class="max-w-3xl mx-auto my-10 p-6 bg-white border border-gray-200 rounded-2xl shadow-sm">
    <h1 class="text-2xl font-bold text-gray-900 mb-2">My Profile</h1>
    <hr class="mb-6 border-gray-100">
    <form wire:submit.prevent="update"
        enctype="multipart/form-data"
    >
        @csrf
        <div class="flex items-center gap-6 mb-6" x-data="{ imageUrl: '{{ $user->avatar ? asset('storage/' . $user->avatar) : 'https://ui-avatars.com/api/?name='.urlencode($user->name) }}' }">
            <div class="relative">
                <img :src="imageUrl" alt="Profile" class="w-24 h-24 rounded-full object-cover border-2 border-gray-100">
            </div>
            <div class="flex flex-col gap-2">
                <div class="flex items-center gap-3">
                    <input type="file" wire:model="avatar" id="avatar" class="hidden" accept="image/*" @change="imageUrl = URL.createObjectURL($event.target.files[0])">
                    <label for="avatar" class="cursor-pointer bg-black hover:bg-gray-800 text-white px-4 py-2 rounded-lg text-sm font-semibold transition flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>Change Image
                    </label>
                </div>
                <p class="text-xs text-gray-500">Only support PNG & JPEG under 2mb</p>
            </div>
        </div>
        <div class="grid grid-cols-1 gap-6 mb-10">
            <div>
                <x-form.field name="name" data-test="profile-name" type='text' label="Full name" placeholder="Enter name" :value="$user->name"></x-form.field>
            </div>
            <div>
                <x-form.field name="bio" data-test="profile-bio" type="textarea" label="Bio" placeholder="Tell us a little about yourself..." :value="$user->bio"></x-form.field>
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
            <a href="{{ route('profile.index') }}" class="group inline-flex items-center gap-2 text-sm font-extrabold text-gray-500 hover:text-gray-800 transition-colors duration-150">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-4 h-4 text-gray-400 group-hover:text-gray-700 group-hover:-translate-x-1 transition-transform duration-150">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
                <span>Back To Profile</span>
            </a>
            <div class="flex items-center gap-3">
                <button type="submit" data-test="update_profile" class="bg-black hover:bg-gray-900 text-white px-8 py-2.5 rounded-xl text-sm font-semibold transition shadow-lg shadow-gray-200">
                    <span wire:loading wire:target="register">Processing...</span>
                    <span wire:loading.remove wire:target="update">Update</span>
                </button>
            </div>
        </div>
    </form>
</div>
