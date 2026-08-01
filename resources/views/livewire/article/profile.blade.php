<?php

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use App\Filament\Resources\Users\RelationManagers\FollowersRelationManager;
use App\Filament\Resources\Users\RelationManagers\FollowingRelationManager;
new class extends Component
{
    public $user;
    public function refresUsersList()
    {
        $this->user = Auth::user();
    }
    public function mount() {
        $this->refresUsersList();
    }
    
    public string $activeTab = 'followers';

    public array $profileRelationManagers = [
        FollowingRelationManager::class,
        FollowersRelationManager::class,
    ];
};
?>

<div class="min-h-screen flex items-center">
    <div class="w-full rounded-3xl p-4 bg-slate-50 dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100 transition-colors duration-200">
        <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-[320px_1fr] gap-8">  
            <div class="bg-white dark:bg-white/5 border border-zinc-200/80 dark:border-white/10 rounded-2xl p-6 shadow-sm h-fit backdrop-blur-xl">
                <div class="flex flex-col items-center text-center">
                    @if ($user->avatar)
                        <img src="{{ asset('storage/' . $user->avatar) }}" class="w-28 h-28 rounded-full object-cover border-2 border-zinc-100 dark:border-white/10 shadow-sm">
                    @else
                        <div class="w-28 h-28 rounded-full bg-zinc-100 dark:bg-white/10 flex items-center justify-center text-2xl font-bold text-zinc-700 dark:text-zinc-200 border border-zinc-200/80 dark:border-white/10 shadow-inner">
                            {{ strtoupper(substr($user->name, 0, 2)) }}
                        </div>
                    @endif
                    <div class="mt-4">
                        <h1 class="text-lg font-bold tracking-tight text-zinc-900 dark:text-white">{{ $user->name }}</h1>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-0.5">{{ $user->email }}</p>
                    </div>
                    <div class="w-full mt-6 bg-zinc-50 dark:bg-white/5 border-b-2 border-zinc-200/60 dark:border-white/10 rounded-lg py-2 text-[11px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Account Details</div>
                </div>

                <div class="mt-4 space-y-3.5 border-t border-zinc-100 dark:border-white/10 pt-4">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-zinc-500 dark:text-zinc-400 font-medium">Joined Date</span>
                        <span class="font-semibold text-zinc-800 dark:text-zinc-200">
                            {{ $user->created_at ? $user->created_at->format('M d, Y') : 'N/A' }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-zinc-500 dark:text-zinc-400 font-medium">Role</span>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-zinc-100 dark:bg-white/10 text-zinc-700 dark:text-zinc-300 border border-zinc-200 dark:border-white/10">
                            {{ $user->getRoleNames()->first() ?? 'Member' }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-zinc-500 dark:text-zinc-400 font-medium">Last Active</span>
                        <span class="font-semibold text-zinc-800 dark:text-zinc-200">
                            {{ $user->last_seen_at ? $user->last_seen_at->diffForHumans() : 'Just now' }}
                        </span>
                    </div>
                </div>
                <a href="/profile" class="block w-full mt-6 bg-zinc-900 dark:bg-white dark:text-zinc-900 hover:bg-zinc-800 dark:hover:bg-zinc-200 text-white text-center rounded-xl py-2.5 text-sm font-semibold shadow-sm transition-colors duration-200">Edit Profile</a>
            </div>

            <div class="space-y-6">
                <div class="bg-white dark:bg-white/5 border border-zinc-200/80 dark:border-white/10 rounded-2xl p-7 shadow-sm backdrop-blur-xl">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6 mb-8">
                        <div>
                            <div class="inline-block px-3 py-1 rounded-md bg-zinc-100 dark:bg-white/10 text-[10px] font-bold uppercase tracking-wider text-zinc-600 dark:text-zinc-300 border border-zinc-200 dark:border-white/10 mb-3">Profile Overview</div>
                            <h2 class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-white">{{ $user->name }}</h2>
                            <p class="mt-1.5 text-sm text-zinc-500 dark:text-zinc-400 leading-relaxed max-w-2xl">Manage your account, articles and audience engagement from your personal dashboard.</p>
                        </div>
                        <a href="/my-articles" class="px-4 py-2.5 rounded-xl bg-zinc-900 dark:bg-white dark:text-zinc-900 hover:bg-zinc-800 dark:hover:bg-zinc-200 text-white text-sm font-semibold tracking-wide shadow-sm transition-colors duration-200 whitespace-nowrap text-center">Manage Articles</a>
                    </div>

                    <div class="border-t border-zinc-100 dark:border-white/10 pt-6 gap-8 sm:gap-12 flex items-center">
                        <a href="/my-articles" class="group">
                            <div class="text-center">
                                <p class="text-xl font-bold text-zinc-900 dark:text-white group-hover:text-zinc-600 dark:group-hover:text-zinc-400 transition-colors">{{ $user->articles->count() }}</p>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">articles</p>
                            </div>
                        </a>
                        <div class="w-px h-7 bg-zinc-200 dark:bg-white/10"></div>
                        <button type="button" wire:click="$set('activeTab', 'followers')" class="group text-left">
                            <div class="text-center">
                                <p class="text-xl font-bold text-zinc-900 dark:text-white group-hover:text-zinc-600 dark:group-hover:text-zinc-400 transition-colors">{{ $user->followers->count() }}</p>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">followers</p>
                            </div>
                        </button>
                        <div class="w-px h-7 bg-zinc-200 dark:bg-white/10"></div>
                        <button type="button" wire:click="$set('activeTab', 'following')" class="group text-left">
                            <div class="text-center">
                                <p class="text-xl font-bold text-zinc-900 dark:text-white group-hover:text-zinc-600 dark:group-hover:text-zinc-400 transition-colors">{{ $user->following->count() }}</p>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">following</p>
                            </div>
                        </button>
                    </div>
                </div>

                <div class="bg-white dark:bg-white/5 border border-zinc-200/80 dark:border-white/10 rounded-2xl p-6 shadow-sm backdrop-blur-xl">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-base font-bold tracking-tight text-zinc-900 dark:text-white">About Me</h3>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">Public Bio</span>
                    </div>
                    <div class="bg-zinc-50 dark:bg-white/5 border border-zinc-200/60 dark:border-white/10 rounded-xl p-4">
                        <p class="text-sm leading-relaxed text-zinc-600 dark:text-zinc-300 font-normal">{{ $user->bio ?? "Hello there, {$user->name}!" }}</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="space-y-4">
            <div class="flex justify-center">
                <x-filament::tabs>
                    <x-filament::tabs.item :active="$activeTab === 'followers'" wire:click="$set('activeTab', 'followers')" :badge="$user->followers->count()">
                        Followers
                    </x-filament::tabs.item>

                    <x-filament::tabs.item :active="$activeTab === 'following'" wire:click="$set('activeTab', 'following')" :badge="$user->following->count()">
                        Following
                    </x-filament::tabs.item>
                </x-filament::tabs>
            </div>

            <div class="mt-4">
                @if ($activeTab === 'followers')
                    <div wire:key="followers-relation-manager">
                        @livewire(FollowersRelationManager::class, [
                            'ownerRecord' => auth()->user(),
                            'pageClass' => \App\Filament\App\Pages\Profile::class,
                        ])
                    </div>
                @elseif ($activeTab === 'following')
                    <div wire:key="following-relation-manager">
                        @livewire(FollowingRelationManager::class, [
                            'ownerRecord' => auth()->user(),
                            'pageClass' => \App\Filament\App\Pages\Profile::class,
                        ])
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>