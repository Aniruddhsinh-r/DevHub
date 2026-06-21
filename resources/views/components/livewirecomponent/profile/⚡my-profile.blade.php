<?php

use Livewire\Component;
use Illuminate\Support\Facades\Auth;


new class extends Component
{
    public $user;
    public function mount() {
        $this->user = Auth::user();
    }
};
?>

<div>
    <div class="min-h-screen py-12 px-4">
        <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-[320px_1fr] gap-8">
            <div class="bg-white border border-gray-200 rounded-[2rem] p-6 shadow-sm h-fit">
                <div class="flex flex-col items-center text-center">
                    @if ($user->avatar)
                        <img src="{{ asset('storage/' . $user->avatar) }}" class="w-32 h-32 rounded-full object-cover border-4 border-[#f3f3f1] shadow-sm">
                    @else
                        <div class="w-32 h-32 rounded-full bg-[#ececea] flex items-center justify-center text-3xl font-black text-[#111111] border-4 border-[#f3f3f1]">
                            {{ substr($user->name, 0, 2) }}
                        </div>
                    @endif

                    <div class="mt-4">
                        <h1 class="text-xl font-black tracking-tight text-[#111111]">{{ $user->name }}</h1>
                        <p class="text-sm text-gray-500 mt-1">{{ $user->email }}</p>
                    </div>

                    <div class="w-full mt-7 border border-gray-300 rounded-xl py-2.5 text-sm font-bold text-[#111111]">
                        Account Details
                    </div>
                </div>

                <div class="mt-4 space-y-4 border-t border-gray-200 pt-2">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-500 font-medium">Joined Date</span>
                        <span class="font-bold text-[#111111]">
                            {{ $user->created_at ? $user->created_at->format('M d, Y') : 'N/A' }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-500 font-medium">role</span>
                        <span class="px-3 py-1 rounded-full text-black text-medium font-bold">
                            {{ $user->getRoleNames()->first() }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-500 font-medium">Last Active</span>
                        <span class="font-bold text-[#111111]">
                            {{ $user->last_seen_at ? $user->last_seen_at->diffForHumans() : 'Just now' }}
                        </span>
                    </div>
                </div>
                <a href="{{ route('profile.edit') }}" class="block w-full mt-8 bg-[#111111] text-white text-center rounded-xl py-3 text-sm font-black tracking-wide hover:bg-black transition-all duration-300">Edit Profile</a>
            </div>

            <div class="space-y-8">
                <div class="bg-[#707577] border border-gray-200 rounded-[2rem] p-8 shadow-sm">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6 mb-8">
                        <div>
                            <div class="inline-block px-3 py-1 rounded-full bg-[#ececea] text-[10px] font-black uppercase tracking-[0.18em] text-gray-700 border border-gray-300 mb-4">
                                Profile Overview
                            </div>
                            <h2 class="text-3xl font-black tracking-tight text-gray-200">
                                {{ $user->name }}
                            </h2>
                            <p class="mt-3 text-sm text-gray-200 leading-7 max-w-2xl">
                                Manage your account, articles and audience engagement from your personal dashboard.
                            </p>
                        </div>

                        <a href="{{ route('publishedarticle') }}" class="px-6 py-3 rounded-2xl bg-[#111111] text-gray-200 text-sm font-black tracking-wide hover:bg-black transition-all duration-300">
                            Manage Articles
                        </a>
                    </div>

                    <div class="border-t border-gray-100 pt-6 gap-6 sm:gap-10 flex items-center">
                        <a href={{ route('publishedarticle') }} class="text-center">
                            <div class="text-center">
                                <p class="text-xl font-black text-gray-200">{{ $user->articles->count() }}</p>
                                <p class="text-[13px] text-gray-200 mt-0.5">articles</p>
                            </div>
                        </a>

                        <div class="w-px h-7 bg-gray-200"></div>

                        <a href="{{ route('followers',$user) }}">
                            <div class="text-center">
                                <p class="text-xl font-black text-gray-200">{{ $user->followers->count() }}</p>
                                <p class="text-[13px] text-gray-200 mt-0.5">followers</p>
                            </div>
                        </a>

                        <div class="w-px h-7 bg-gray-200"></div>

                        <a href="{{ route('followings',$user) }}">
                            <div class="text-center">
                                <p class="text-xl font-black text-gray-200">{{ $user->following->count() }}</p>
                                <p class="text-[13px] text-gray-200 mt-0.5">following</p>
                            </div>
                        </a>
                    </div>
                </div>

                <div class="bg-white border border-gray-200 rounded-[2rem] p-6 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xl font-black tracking-tight text-[#111111]">About Me</h3>
                        <span class="text-[10px] font-black uppercase tracking-[0.18em] text-gray-400">Public Bio</span>
                    </div>

                    <div class="bg-[#f5f5f3] border border-gray-200 rounded-2xl p-5">
                        <p class="text-sm leading-7 text-gray-600 font-medium">{{ $user->bio ?? "Hello there {$user->name}" }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
