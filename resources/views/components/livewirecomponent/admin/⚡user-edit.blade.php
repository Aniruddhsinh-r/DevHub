<?php
use Livewire\Component;
use App\Models\User;
use App\Enums\UserRole;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Support\Facades\Storage;

new #[Layout('layouts::dashboard')] class extends Component
{
    use WithFileUploads;

    public User $user;
    #[Validate('required|min:5|max:50')]
    public $name;
    #[Validate]
    public $email;
    #[Validate('nullable|max:2000|string')]
    public $bio;
    #[Validate]
    public $role = false;
    #[Validate('nullable|image|mimes:jpeg,png,jpg,gif|max:2048')]
    public $avatar;
    public $delete_avatar = false;

    public function mount(User $user) {
        $this->user = $user;
        $this->name = $this->user->name;
        $this->email = $this->user->email;
        $this->bio = $this->user->bio;
        $this->role = $this->user->getRoleNames()->first();
    }

    public function rules() {
        return [
            'email' => 'required|email|min:10|max:255|unique:users,email,' . $this->user->id,
            'role' => ['nullable', new Enum(UserRole::class)],
        ];
    }

    public function removeAvatar() {
        $this->avatar = null;
        $this->delete_avatar = true;
    }

    public function update() {
        $values = $this->validate();

        $data = [
            'name' => $values['name'],
            'email' => $values['email'],
            'bio' => $values['bio'],
        ];

        if (!empty($values['role'])) {
            $this->user->syncRoles($values['role']);
        }

        if ($this->delete_avatar) {
            if ($this->user->avatar) {
                Storage::disk('public')->delete($this->user->avatar);
            }
            $data['avatar'] = null;
        }

        if (!empty($values['avatar'])) {
            $data['avatar'] = $values['avatar']->store('avatars', 'public');
            if ($this->user->avatar && !$this->delete_avatar) {
                Storage::disk('public')->delete($this->user->avatar);
            }
        }

        if ($this->user->update($data)) {
            session()->flash('success', 'This user profile updated successfully.');
            return $this->redirectRoute('admin.users', navigate: true);
        }

        $this->dispatch('live-notification', message: 'Failed to update profile.');
    }
};
?>

<div class="min-h-screen bg-[#f9fafb] py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto">
        <div class="mb-8">
            <h1 class="text-3xl font-extrabold tracking-tight text-gray-900">Account Settings</h1>
            <p class="mt-2 text-sm text-gray-600">Update your public identity, email address, and security credentials.</p>
        </div>

        <form wire:submit.prevent="update" enctype="multipart/form-data" class="space-y-8">
            @csrf
            <div class="bg-white border border-gray-200 shadow-sm rounded-2xl overflow-hidden">
                <div class="p-6 sm:p-8 space-y-6">
                    <div class="border-b border-gray-100 pb-4">
                        <h2 class="text-lg font-bold text-gray-900">Profile Details</h2>
                        <p class="text-xs text-gray-500 mt-1">This information will be displayed across the system platform.</p>
                    </div>

                    <div class="flex flex-col sm:flex-row items-center gap-6" x-data="{ imageUrl: '{{ $user->avatar ? asset('storage/' . $user->avatar) : 'https://ui-avatars.com/api/?name='.urlencode($user->name) }}' }">
                        <div class="relative group">
                            <img :src="imageUrl" alt="Profile" class="w-24 h-24 rounded-full object-cover border-4 border-gray-50 shadow-inner">
                        </div>

                        <div class="flex flex-col items-center sm:items-start gap-2 w-full sm:w-auto">
                            <div class="flex items-center gap-3">
                                <input type="file" wire:model="avatar" id="avatar" x-ref="avatarInput" class="hidden" accept="image/*" @change="imageUrl = URL.createObjectURL($event.target.files[0])">
                                <div class="flex items-center gap-3">
                                    <label for="avatar" class="cursor-pointer bg-black hover:bg-gray-800 text-white px-4 py-2 rounded-lg text-sm font-semibold transition flex items-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                        </svg>Change Image
                                    </label>
                                    <button type="button" wire:click="removeAvatar" @click="imageUrl = 'https://ui-avatars.com/api/?name={{ urlencode($user->name) }}'; $refs.avatarInput.value = '';" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition">
                                        Remove
                                    </button>
                                </div>
                            </div>
                            @error('avatar')
                                <p class="text-xs text-red-500 font-semibold mt-1">{{ $message }}</p>
                            @else
                                <p class="text-xs text-gray-500">Only support PNG & JPEG under 2mb</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <x-form.field name="name" data-test="profile-name" type='text' label="Full name" placeholder="Enter name" :value="$user->name"></x-form.field>
                        </div>

                        <div>
                            <x-form.field name="email" data-test="profile-gmail" type="email" label="Your email" placeholder="mailadd@gmail.com" :value="$user->email"></x-form.field>
                        </div>

                    </div>
                    <div>
                        <x-form.field name="bio" data-test="profile-bio" type="textarea" label="Bio" placeholder="Tell us a little about yourself..." :value="$user->bio"></x-form.field>
                    </div>
                </div>
            </div>

            <div class="bg-white border border-gray-200 shadow-sm rounded-2xl overflow-hidden">
                <div class="p-6 sm:p-8 space-y-6">
                    <div class="border-b border-gray-100 pb-4">
                        <h2 class="text-lg font-bold text-gray-900">Security Credentials</h2>
                        <p class="text-xs text-gray-500 mt-1">Leave these fields completely blank if you do not want to modify your password.</p>
                    </div>

                    <div>
                        <label class="block text-xs font-black uppercase tracking-wider text-gray-700 mb-2">Role</label>
                        <select wire:model="role" class="w-full bg-gray-50/50 border border-gray-200 rounded-xl px-4 py-3 text-sm font-medium text-gray-900 focus:bg-white focus:ring-2 focus:ring-black focus:border-black transition-all outline-none">
                            <option value="">Select a role</option>
                            <option value="admin">Admin</option>
                            <option value="author">Author</option>
                        </select>
                        @error('role') <p class="text-xs text-red-500 mt-1 font-medium">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-4 border-t border-gray-200">
                <a href="{{ route('admin.users') }}" wire:navigate class="group inline-flex items-center gap-2 text-sm font-bold text-gray-500 hover:text-gray-900 transition-colors duration-150 order-2 sm:order-1">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 text-gray-400 group-hover:text-gray-900 group-hover:-translate-x-0.5 transition-transform">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                    Cancel and Return
                </a>

                <button type="submit" class="w-full sm:w-auto bg-black hover:bg-gray-900 text-white px-8 py-3 rounded-xl text-sm font-bold transition shadow-md shadow-gray-100 order-1 sm:order-2 flex items-center justify-center">
                    <span wire:loading wire:target="update" class="inline-block animate-spin mr-2 h-4 w-4 border-2 border-white border-t-transparent rounded-full"></span>
                    <span wire:loading wire:target="update">Saving changes...</span>
                    <span wire:loading.remove wire:target="update">Save Configurations</span>
                </button>
            </div>
        </form>
    </div>
</div>
