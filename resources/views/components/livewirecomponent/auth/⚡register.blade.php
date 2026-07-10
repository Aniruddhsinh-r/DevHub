<?php

use Livewire\Component;
use App\Actions\UserRegister;
use Livewire\WithFileUploads;
use Livewire\Attributes\Sensitive;
use Livewire\Attributes\Validate;

new class extends Component
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
    #[Validate('required|string|min:8|max:255')]
    public $password = '';

    public function rules() {
        return [
            'email' => 'required|email|min:10|max:255|unique:users,email,',
        ];
    }

    public function register(UserRegister $action) {
        $values = $this->validate();

        $action->handle($values);

        session()->flash('success', 'Account created successfully.');
        return $this->redirectRoute('home', navigate: true);
    }
};
?>

<div>
    {{-- Fonts: Fraunces for the display voice on the brand panel, Inter for the form/body --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400;0,9..144,500;0,9..144,600;1,9..144,500&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
 
    <style>
        .font-display { font-family: 'Fraunces', ui-serif, Georgia, serif; }
        .font-body { font-family: 'Inter', ui-sans-serif, system-ui, sans-serif; }
 
        .brand-panel {
            background:
                radial-gradient(60% 50% at 15% 10%, rgba(255, 255, 255, 0.10), transparent 60%),
                radial-gradient(70% 60% at 90% 90%, rgba(255, 255, 255, 0.05), transparent 55%),
                linear-gradient(160deg, #0B0B12 0%, #1A1A1A 55%, #2B2B2B 100%);
        }
 
        @keyframes orbit-slow { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
        @keyframes orbit-fast { from { transform: rotate(360deg); } to { transform: rotate(0deg); } }
        @keyframes node-pulse { 0%, 100% { opacity: .55; } 50% { opacity: 1; } }
 
        .orbit-ring-outer { animation: orbit-slow 34s linear infinite; transform-origin: 160px 160px; }
        .orbit-ring-inner { animation: orbit-fast 22s linear infinite; transform-origin: 160px 160px; }
        .orbit-node { animation: node-pulse 3.2s ease-in-out infinite; }
 
        @media (prefers-reduced-motion: reduce) {
            .orbit-ring-outer, .orbit-ring-inner, .orbit-node { animation: none !important; }
        }
 
        .avatar-drop:hover .avatar-drop-inner { border-color: #2E2E2E; background-color: rgba(0, 0, 0, 0.03); }
    </style>
 
    <div class="bg-[#F5F5F5] font-body flex mx-4 md:mx-24 my-6 rounded-4xl overflow-hidden">
        <div class="brand-panel relative hidden lg:flex lg:w-[44%] flex-col justify-between overflow-hidden px-14 py-12 text-white">
            <div class="relative z-10 flex items-center gap-2.5">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-white/10 ring-1 ring-white/15">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" class="h-4.5 w-4.5">
                        <circle cx="12" cy="12" r="3.2" />
                        <circle cx="12" cy="4" r="1.4" fill="currentColor" stroke="none" />
                        <circle cx="19.5" cy="16" r="1.4" fill="currentColor" stroke="none" />
                        <circle cx="4.5" cy="16" r="1.4" fill="currentColor" stroke="none" />
                    </svg>
                </span>
                <span class="font-display text-lg tracking-tight">DevHub</span>
            </div>
 
            <div class="relative z-10 my-auto flex justify-center">
                <svg viewBox="0 0 320 320" class="h-64 w-64" aria-hidden="true">
                    <circle cx="160" cy="160" r="118" fill="none" stroke="white" stroke-opacity="0.07" />
                    <circle cx="160" cy="160" r="82" fill="none" stroke="white" stroke-opacity="0.09" />
 
                    <g class="orbit-ring-outer">
                        <line x1="160" y1="160" x2="160" y2="42" stroke="white" stroke-opacity="0.15" />
                        <circle class="orbit-node" cx="160" cy="42" r="5" fill="#E5E5E5" />
                    </g>
                    <g class="orbit-ring-outer" style="animation-delay:-11s">
                        <line x1="160" y1="160" x2="278" y2="160" stroke="white" stroke-opacity="0.12" />
                        <circle class="orbit-node" cx="278" cy="160" r="4" fill="#9CA3AF" style="animation-delay:-1s" />
                    </g>
                    <g class="orbit-ring-inner">
                        <line x1="160" y1="160" x2="98" y2="238" stroke="white" stroke-opacity="0.15" />
                        <circle class="orbit-node" cx="98" cy="238" r="4.5" fill="white" style="animation-delay:-2s" />
                    </g>
                    <g class="orbit-ring-inner" style="animation-delay:-7s">
                        <line x1="160" y1="160" x2="222" y2="82" stroke="white" stroke-opacity="0.12" />
                        <circle class="orbit-node" cx="222" cy="82" r="4" fill="#9CA3AF" style="animation-delay:-1.5s" />
                    </g>
 
                    <circle cx="160" cy="160" r="15" fill="#2E2E2E" />
                    <circle cx="160" cy="160" r="15" fill="none" stroke="white" stroke-opacity="0.4" stroke-width="1.5">
                        <animate attributeName="r" values="15;24;15" dur="3.2s" repeatCount="indefinite" />
                        <animate attributeName="stroke-opacity" values="0.4;0;0.4" dur="3.2s" repeatCount="indefinite" />
                    </circle>
                </svg>
            </div>
 
            <div class="relative z-10 space-y-6">
                <p class="font<div class="relative z-10 space-y-6">
                <p class="font-display text-[28px] leading-snug tracking-tight">Share your ideas,<br class="hidden xl:block"> inspire every developer.</p>
                <ul class="space-y-3 text-sm text-white/70">
                    <li class="flex items-start gap-2.5">
                        <svg class="mt-0.5 h-4 w-4 flex-none text-[#9CA3AF]" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.7 5.3a1 1 0 010 1.4l-7.4 7.4a1 1 0 01-1.4 0L3.3 9.5a1 1 0 111.4-1.4l3.9 3.9 6.7-6.7a1 1 0 011.4 0z" clip-rule="evenodd"/></svg>
                        <span>Publish articles and share your knowledge with the community.</span>
                    </li>

                    <li class="flex items-start gap-2.5">
                        <svg class="mt-0.5 h-4 w-4 flex-none text-[#9CA3AF]" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.7 5.3a1 1 0 010 1.4l-7.4 7.4a1 1 0 01-1.4 0L3.3 9.5a1 1 0 111.4-1.4l3.9 3.9 6.7-6.7a1 1 0 011.4 0z" clip-rule="evenodd"/></svg>
                        <span>Discover developers, follow profiles, and explore their latest articles.</span>
                    </li>

                    <li class="flex items-start gap-2.5">
                        <svg class="mt-0.5 h-4 w-4 flex-none text-[#9CA3AF]" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.7 5.3a1 1 0 010 1.4l-7.4 7.4a1 1 0 01-1.4 0L3.3 9.5a1 1 0 111.4-1.4l3.9 3.9 6.7-6.7a1 1 0 011.4 0z" clip-rule="evenodd"/></svg>
                        <span>Join discussions with comments and enjoy a personalized experience.</span>
                    </li>
                </ul>
            </div>
        </div>
 
        <div class="flex w-full flex-1 flex-col justify-center px-6 py-8 sm:px-10 lg:px-16 xl:px-24">
            <div class="mx-auto w-full max-w-sm">
                <div class="mb-9 lg:hidden flex items-center gap-2.5">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#2E2E2E]/10 ring-1 ring-[#2E2E2E]/20">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#2E2E2E" stroke-width="1.75" class="h-4.5 w-4.5">
                            <circle cx="12" cy="12" r="3.2" />
                            <circle cx="12" cy="4" r="1.4" fill="#2E2E2E" stroke="none" />
                            <circle cx="19.5" cy="16" r="1.4" fill="#2E2E2E" stroke="none" />
                            <circle cx="4.5" cy="16" r="1.4" fill="#2E2E2E" stroke="none" />
                        </svg>
                    </span>
                    <span class="font-display text-lg tracking-tight text-[#0B0B12]">DevHub</span>
                </div>
 
                <h2 class="font-display text-[32px] leading-tight tracking-tight text-[#0B0B12]">Create your account</h2>
                <p class="mt-2 text-sm text-gray-500">Already in the network?
                    <a href="{{ route('login') }}" wire:navigate class="font-semibold text-[#2E2E2E] hover:text-[#000000] transition-colors">Sign in instead</a>
                </p>

                <form wire:submit.prevent="register" class="mt-8 space-y-5" enctype="multipart/form-data">
                    @csrf
                    <x-form.field name="name" autocomplete="name" type="text" label="Full name" placeholder="Enter Name"></x-form.field>
 
                    <x-form.field name="email" type="email" label="Your email" placeholder="mailadd@gmail.com"></x-form.field>
 
                    <x-form.field name="password" type="password" label="Password" placeholder="••••••••"></x-form.field>
 
                    <x-form.field name="bio" type="textarea" label="Bio" placeholder="Tell us a little about yourself..."></x-form.field>
 
                    <div x-data="{ preview: null, fileName: null }" class="space-y-1.5">
                        <label for="avatar" class="block text-xs font-bold uppercase tracking-wider text-gray-700">Profile picture</label> 
                        <label for="avatar" class="avatar-drop group block cursor-pointer">
                            <div class="avatar-drop-inner flex items-center gap-4 rounded-xl border-2 border-dashed border-gray-200 bg-gray-50/60 px-4 py-3.5 transition-colors duration-150">
                                <div class="relative flex h-12 w-12 flex-none items-center justify-center overflow-hidden rounded-full bg-white ring-1 ring-gray-200">
                                    <template x-if="preview">
                                        <img :src="preview" alt="" class="h-full w-full object-cover">
                                    </template>
                                    <template x-if="!preview">
                                        <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 2a4 4 0 100 8 4 4 0 000-8zM3 18a7 7 0 1114 0H3z" clip-rule="evenodd" />
                                        </svg>
                                    </template>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-semibold text-gray-700" x-text="fileName || 'Choose a photo'"></p>
                                    <p class="text-xs text-gray-400">JPG, PNG or GIF · up to 2MB</p>
                                </div>
                                <span class="flex-none rounded-lg bg-gray-700 px-3 py-1.5 text-xs font-semibold text-white group-hover:bg-[#2E2E2E] transition-colors duration-150">
                                    Browse
                                </span>
                            </div>
 
                            <input type="file" id="avatar" name="avatar" wire:model="avatar" x-on:change=" const f = $event.target.files[0]; if (f) { preview = URL.createObjectURL(f); fileName = f.name; }"class="sr-only"/>
                        </label>
                        @error('avatar') <span class="text-xs font-medium text-red-500">{{ $message }}</span> @enderror
                    </div>
 
                    <div class="pt-3">
                        <button type="submit" wire:loading.attr="disabled" data-test="registerBTN" class="inline-flex w-full cursor-pointer items-center justify-center gap-2 rounded-xl bg-[#2E2E2E] px-6 py-3 text-sm font-semibold text-white shadow-[0_8px_24px_-8px_rgba(0,0,0,0.35)] transition-all duration-200 hover:bg-[#000000] hover:shadow-[0_10px_28px_-8px_rgba(0,0,0,0.45)] focus:outline-none focus:ring-2 focus:ring-[#2E2E2E] focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-70">
                            <svg wire:loading wire:target="register" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                            </svg>
                            <span wire:loading wire:target="register">Processing...</span>
                            <span wire:loading.remove wire:target="register">Create account</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
