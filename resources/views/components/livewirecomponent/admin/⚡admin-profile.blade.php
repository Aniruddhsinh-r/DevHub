<?php

use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;

new #[Layout('layouts::dashboard')] class extends Component
{
    public $admin;

    public function mount()
    {
        $this->admin = Auth::user();
    }
};
?>

<div>
    <div class="min-h-screen py-12 px-4">
        <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-[320px_1fr] gap-8">

            <!-- Left Sidebar (Kept exactly the same) -->
            <div class="bg-white border border-gray-200 rounded-[2rem] p-6 shadow-sm h-fit">
                <div class="flex flex-col items-center text-center">
                    @if ($admin->avatar)
                        <img src="{{ asset('storage/' . $admin->avatar) }}" class="w-32 h-32 rounded-full object-cover border-4 border-[#f3f3f1] shadow-sm">
                    @else
                        <div class="w-32 h-32 rounded-full bg-[#ececea] flex items-center justify-center text-3xl font-black text-[#111111] border-4 border-[#f3f3f1]">
                            {{ substr($admin->name, 0, 2) }}
                        </div>
                    @endif

                    <div class="mt-4">
                        <h1 class="text-xl font-black tracking-tight text-[#111111]">{{ $admin->name }}</h1>
                        <p class="text-sm text-gray-500 mt-1">{{ $admin->email }}</p>
                    </div>

                    <div class="w-full mt-7 border border-gray-300 rounded-xl py-2.5 text-sm font-bold text-[#111111]">Account Details</div>
                </div>

                <div class="mt-4 space-y-4 border-t border-gray-200 pt-2">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-500 font-medium">Joined Date</span>
                        <span class="font-bold text-[#111111]">
                            {{ $admin->created_at ? $admin->created_at->format('M d, Y') : 'N/A' }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-500 font-medium">role</span>
                        <span class="px-3 py-1 rounded-full text-black text-medium font-bold">
                            {{ $admin->getRoleNames()->first() }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-500 font-medium">Last Active</span>
                        <span class="font-bold text-[#111111]">
                            {{ $admin->last_seen_at ? $admin->last_seen_at->diffForHumans() : 'Just now' }}
                        </span>
                    </div>
                </div>
                <a href="{{ route('admin-profile.edit') }}" wire:navigate class="block w-full mt-8 bg-[#111111] text-white text-center rounded-xl py-3 text-sm font-black tracking-wide hover:bg-black transition-all duration-300">Edit Profile</a>
            </div>

            <!-- Right Main Panel -->
            <div class="space-y-8">
                <!-- Profile Overview Box -->
                <div class="bg-[#707577] border border-gray-200 rounded-[2rem] p-8 shadow-sm">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                        <div>
                            <div class="inline-block px-3 py-1 rounded-full bg-[#ececea] text-[10px] font-black uppercase tracking-[0.18em] text-gray-700 border border-gray-300 mb-4">Profile Overview</div>
                            <h2 class="text-3xl font-black tracking-tight text-gray-200">
                                Welcome back, {{ strtok($admin->name, ' ') }}
                            </h2>
                            <p class="mt-3 text-sm text-gray-200 leading-7 max-w-2xl">This is your private administrative hub. Monitor system integrity, look over active sessions, and execute quick security configurations below.</p>
                        </div>
                    </div>
                </div>

                <!-- NEW SECTION: Admin System Status Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="bg-white border border-gray-200 rounded-2xl p-5 shadow-sm">
                        <p class="text-xs font-black text-gray-400 uppercase tracking-wider">System Status</p>
                        <div class="flex items-center gap-2 mt-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span class="text-lg font-bold text-gray-900">Operational</span>
                        </div>
                    </div>

                    <div class="bg-white border border-gray-200 rounded-2xl p-5 shadow-sm">
                        <p class="text-xs font-black text-gray-400 uppercase tracking-wider">Security Profile</p>
                        <div class="flex items-center gap-2 mt-2">
                            <span class="text-lg font-bold text-gray-900">Enhanced (SSL)</span>
                        </div>
                    </div>

                    <div class="bg-white border border-gray-200 rounded-2xl p-5 shadow-sm">
                        <p class="text-xs font-black text-gray-400 uppercase tracking-wider">Active Sessions</p>
                        <div class="flex items-center gap-2 mt-2">
                            <span class="text-lg font-bold text-gray-900">1 Device</span>
                        </div>
                    </div>
                </div>

                <!-- NEW SECTION: Management Shortcuts -->
                <div class="bg-white border border-gray-200 rounded-[2rem] p-6 shadow-sm">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-black tracking-tight text-[#111111]">Quick Administrative Actions</h3>
                        <span class="text-[10px] font-black uppercase tracking-[0.18em] text-gray-400">Controls</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <a href="{{ route('admin.articles') }}" class="group flex items-center justify-between bg-[#f5f5f3] border border-gray-200 rounded-xl p-4 hover:bg-[#ececea] transition-colors duration-200">
                            <div class="flex items-center gap-3">
                                <div class="bg-white p-2 rounded-lg border border-gray-200 shadow-sm text-gray-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="text-sm font-bold text-gray-900">Article changes</h4>
                                    <p class="text-xs text-gray-500 mt-0.5">Review Article changes and activity tracks</p>
                                </div>
                            </div>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 text-gray-400 group-hover:translate-x-1 transition-transform">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                            </svg>
                        </a>

                        <a href="{{ route('admin.users') }}" class="group flex items-center justify-between bg-[#f5f5f3] border border-gray-200 rounded-xl p-4 hover:bg-[#ececea] transition-colors duration-200">
                            <div class="flex items-center gap-3">
                                <div class="bg-white p-2 rounded-lg border border-gray-200 shadow-sm text-gray-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.43l-1.003.767c-.31.236-.456.63-.417 1.01.004.04.004.08.004.122 0 .04 0 .08-.004.122-.039.38.107.774.417 1.01l1.003.767a1.125 1.125 0 0 1 .26 1.43l-1.296 2.247a1.125 1.125 0 0 1-1.37.49l-1.216-.456c-.356-.133-.751-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.43l1.004-.767c.31-.236.456-.63.416-1.01a4 4 0 0 1-.004-.122c0-.04 0-.08.004-.122.04-.38-.106-.774-.416-1.01l-1.004-.767a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.49l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="text-sm font-bold text-gray-900">Global User Activitys</h4>
                                    <p class="text-xs text-gray-500 mt-0.5">Configure system metrics and variables</p>
                                </div>
                            </div>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 text-gray-400 group-hover:translate-x-1 transition-transform">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
