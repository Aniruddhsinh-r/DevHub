<?php

use Livewire\Component;
use App\Models\User;
use App\Enums\UserRole;
use Livewire\Attributes\Layout;
use Illuminate\Validation\Rule;
use Livewire\WithFileUploads;
use Livewire\Attributes\Sensitive;
use Livewire\Attributes\Validate;

new #[Layout('layouts::dashboard')] class extends Component
{
    use WithFileUploads;

    #[Validate('required|min:5|max:50')]
    public $name = '';
    #[Validate]
    public $email = '';
    #[Validate('nullable|image|mimes:jpeg,png,jpg,gif|max:2048')]
    public $avatar = null;
    #[Validate('nullable|max:2000|string')]
    public $bio = '';
    #[Sensitive]
    #[Validate('nullable|string|min:8|max:255')]
    public $password = '';

    public function rules() {
        return [
            'email' => 'required|email|min:10|max:255|unique:users,email,',
        ];
    }

    public function register() {
        $values = $this->validate();

        $avatarPath = null;
        if ($values['avatar'] ?? false) {
            $avatarPath = $values['avatar']->store('avatars', 'public');
        }

        $user = User::create([
            'name' => $values['name'],
            'email' => strtolower($values['email']),
            'password' => Hash::make($values['password']),
            'bio' => $values['bio'],
            'avatar'=> $avatarPath,
        ]);

        $user->assignRole(UserRole::AUTHOR);
        session()->flash('success', 'Account created successfully.');
        return $this->redirectRoute('admin.users', navigate: true);
    }
};
?>

<div>
    <div class="min-h-screen flex flex-col justify-center sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-md">
            <h2 class="text-center text-3xl font-extrabold tracking-tight text-gray-900">
                Create user account
            </h2>
        </div>

        <div class="mt-6 sm:mx-auto sm:w-full sm:max-w-md">
            <div class="bg-white py-8 px-4 shadow-sm border border-gray-100 sm:rounded-2xl sm:px-10">
                <form wire:submit.prevent="register" class="space-y-5"
                    enctype="multipart/form-data"
                >
                    @csrf
                    <x-form.field name="name" type="text" label="Full name" placeholder="Enter Name"></x-form.field>

                    <x-form.field name="email" type="email" label="Your email" placeholder="mailadd@gmail.com"></x-form.field>

                    <x-form.field name="password" type="password" label="Password" placeholder="••••••••"></x-form.field>

                    <x-form.field name="bio" type="textarea" label="Bio" placeholder="Tell us a little about yourself..."></x-form.field>

                    <div class='space-y-0.5'>
                        <label for="avatar" class="block text-xs font-bold uppercase tracking-wider text-gray-700">Profile Pic</label>
                        <input type="file" id="avatar" name="avatar" wire:model="avatar" class="border border-gray-400 w-full font-medium text-sm text-gray-700 rounded-md shadow-xs cursor-pointer file:bg-gray-700 file:text-white file:px-4 file:py-2 file:rounded-l-md file:border-0" placeholder="Profile Pic" />
                    </div>

                    <div class="mt-8">
                        <button type="submit" wire:loading.attr="disabled" data-test="registerBTN" class="inline-flex justify-center py-2.5 w-full border border-transparent rounded-xl shadow-sm text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200">
                            <span wire:loading wire:target="register">Processing...</span>
                            <span wire:loading.remove wire:target="register">Register</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
