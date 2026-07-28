<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        body { font-family: 'Inter', sans-serif; }
        .glass-effect { background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(10px); }
    </style>
</head>
<body class="bg-[#f8f9fa] text-[#1a1a1a] min-h-screen flex flex-col">
    <main>
        <nav x-data="{ mobileMenuOpen: false }" class="sticky top-0 z-50 border-b border-gray-200 glass-effect">
            <div class="mx-auto px-4 sm:px-18">
                <div class="flex justify-between items-center h-16">
                    <div class="flex items-center">
                        <a href="{{ route('filament.app.pages.home') }}" class="text-2xl font-bold tracking-tighter italic">DevHub</a>
                    </div>
                    <div class="flex items-center gap-4 md:gap-8">
                        <a href="{{ route('filament.app.pages.home') }}" wire:navigate class="hidden md:block text-sm font-medium hover:text-gray-500 transition">Home</a>
                        <a href="/app/article" wire:navigate class="hidden md:block text-sm font-medium hover:text-gray-500 transition">Articles</a>

                        @auth
                            @role('author')
                                <a href="{{ route('profile.index') }}" wire:navigate class="hidden md:block text-sm font-medium hover:text-gray-500 transition">Profile</a>
                                <a href="{{ route('articles.create') }}" wire:navigate class="hidden sm:inline-flex items-center px-5 py-2.5 text-sm font-semibold text-white bg-[#1a1a1a] rounded-full hover:bg-gray-800 transition-all duration-300 shadow-sm">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                    </svg>Create article
                                </a>
                            @endrole

                            <div x-data="{ open: false }" data-test="Authbutton" class="relative z-50">
                                <button @click="open = !open" @click.outside="open = false" class="flex cursor-pointer items-center focus:outline-none">
                                    @if (auth()->user()->avatar)
                                        <img src="{{ asset('storage/' . auth()->user()->avatar) }}" alt="User Profile" class="w-10 h-10 rounded-full object-cover border-2 border-gray-200 hover:border-gray-400 transition">
                                    @else
                                        <span class="w-10 h-10 rounded-full bg-black text-white border border-gray-400 inline-flex items-center justify-center text-sm font-bold uppercase">
                                            {{ Str::upper(Str::substr(auth()->user()->name, 0, 2)) }}
                                        </span>
                                    @endif
                                </button>
                                <div x-show="open"
                                    x-transition:enter="transition ease-out duration-100"
                                    x-transition:enter-start="opacity-0 scale-95"
                                    x-transition:enter-end="opacity-100 scale-100"
                                    x-transition:leave="transition ease-in duration-75"
                                    x-transition:leave-start="opacity-100 scale-100"
                                    x-transition:leave-end="opacity-0 scale-95"
                                    class="absolute right-0 mt-2 w-64 bg-white rounded-xl shadow-xl border border-gray-100 py-3 text-left"
                                    style="display: none;">
                                    <div class="px-4 font-semibold break-all">{{ auth()->user()->email }}</div>
                                    <hr class="border-gray-100 my-1">
                                    @role('author')
                                        <a href="{{ route('show.bookmarks') }}" wire:navigate class="w-full flex items-center gap-3 px-5 py-2 text-sm text-gray-700 font-medium hover:text-gray-500 transition">
                                            <svg class="w-5 h-5 text-gray-800" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                                            </svg>Bookmark
                                        </a>
                                        <a href="{{ route('publishedarticle') }}" wire:navigate class="w-full flex items-center gap-3 px-5 py-2 text-sm text-gray-700 font-medium hover:text-gray-500 transition">
                                            <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"></path>
                                            </svg>My Articles
                                        </a>
                                    @endrole
                                    <form action="{{ route('logout') }}" method="POST" class="w-full">
                                        @csrf
                                        <button type="submit" data-test="logout" class="w-full cursor-pointer flex items-center gap-3 px-5 py-2 text-sm text-gray-700 font-medium hover:text-gray-500 transition">
                                            <svg class="w-5 h-5 text-gray-800" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                            </svg>Logout
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endauth

                        @guest
                            <a href="{{ route('register.create') }}" wire:navigate class="hidden md:block text-sm font-medium hover:text-gray-500 transition">Sign Up</a>
                            <a href="{{ route('login') }}" wire:navigate class="inline-flex items-center px-5 py-2.5 text-sm font-semibold text-white bg-[#1a1a1a] rounded-full hover:bg-gray-800 transition-all duration-300 shadow-sm">Sign In</a>
                        @endguest

                        <button @click="mobileMenuOpen = !mobileMenuOpen" class="block md:hidden text-[#1a1a1a] focus:outline-none z-50">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path x-show="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                                <path x-show="mobileMenuOpen" x-cloak stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
            <div x-show="mobileMenuOpen"
                x-transition:enter="transition ease-out duration-200"
                class="md:hidden border-b border-gray-200 bg-white/95 backdrop-blur-lg absolute left-0 right-0 top-16 shadow-lg py-4 px-6 flex flex-col gap-4 z-40"
                style="display: none;">
                <a href="{{ route('filament.app.pages.home') }}" wire:navigate class="text-base font-semibold hover:text-gray-500 transition py-2 border-b border-gray-50">Home</a>
                <a href="/app/articles" wire:navigate class="text-base font-semibold hover:text-gray-500 transition py-2 border-b border-gray-50">Articles</a>
                @role('author')
                    <a href="{{ route('profile.index') }}" wire:navigate class="text-base font-semibold hover:text-gray-500 transition py-2 border-b border-gray-50">Profile</a>
                    <a href="/app/articles/crete" wire:navigate class="text-base font-semibold hover:text-gray-500 transition py-2 border-b border-gray-50">Create article</a>
                @endrole
                @guest
                    <a href="{{ route('register.create') }}" wire:navigate class="text-base font-semibold hover:text-gray-500 transition py-2">Sign Up</a>
                @endguest
            </div>
        </nav>

        <div class="text-black">
            {{ $slot }}
        </div>

        <livewire:livewirecomponent.home.delete-popup/>

        @if (session()->has('success'))
            <div data-test="success-message" class="hidden">{{ session('success') }}</div>
            <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 4000)" x-show="show" x-transition
                class="fixed top-5 right-5 bg-green-600 text-white px-6 py-3 rounded-md shadow-lg z-50 flex items-center space-x-3">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 8" />
                </svg>
                <span class="text-sm font-medium tracking-wide">{{ session('success') }}</span>
            </div>
        @endif

        @if (session()->has('error'))
            <div data-test="error-message" class="hidden">{{ session('error') }}</div>
            <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 4000)" x-show="show" x-transition
                class="fixed top-5 right-5 bg-red-600 text-white px-6 py-3 rounded-md shadow-lg z-50 flex items-center space-x-3">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <span class="text-sm font-medium tracking-wide">{{ session('error') }}</span>
            </div>
        @endif
    </main>

    <footer class="bg-white border-t border-gray-100 mt-auto">
        <div class="max-w-7xl mx-auto px-4 pt-12 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-8 md:flex-row md:justify-between md:items-start">
                <div class="space-y-4 md:w-1/3">
                    <a href="{{ route('filament.app.pages.home') }}" wire:navigate class="text-xl font-black tracking-tight text-black flex items-center gap-2"><span>Dev<span class="text-indigo-600">Hub</span></span></a>
                    <p class="text-gray-500 text-sm max-w-sm font-medium leading-relaxed">A token-space ecosystem for developers and creators to share insights, build followings, and discover top technical content.</p>
                </div>

                <div class="md:w-1/4">
                    <h3 class="text-xs font-semibold text-slate-400 tracking-wider uppercase">EXPLORE</h3>
                    <ul role="list" class="mt-4 space-y-3">
                        <li>
                            <a href="/app/articles" wire:navigate class="text-sm font-bold text-slate-700 hover:text-black transition-colors duration-150">Latest articles</a>
                        </li>
                        @auth
                            <li>
                                <a href="{{ route('followings',auth()->user()) }}" wire:navigate class="text-sm font-bold text-slate-700 hover:text-black transition-colors duration-150">Following list</a>
                            </li>
                            <li>
                                <a href="{{ route('profile.index') }}" wire:navigate class="text-sm font-bold text-slate-700 hover:text-black transition-colors duration-150">Your profile</a>
                            </li>
                            @else
                            <li>
                                <a href="{{ route('login') }}" wire:navigate class="text-sm font-bold text-slate-700 hover:text-black transition-colors duration-150">Sign In</a>
                            </li>
                            <li>
                                <a href="{{ route('register.create') }}" wire:navigate class="text-sm font-bold text-slate-700 hover:text-black transition-colors duration-150">Create account</a>
                            </li>
                        @endauth
                    </ul>
                </div>

                <div class="md:w-1/4">
                    <h3 class="text-xs font-semibold text-slate-400 tracking-wider uppercase">PLATFORM INFO</h3>
                    <p class="mt-4 text-sm font-medium text-slate-400 leading-relaxed">Built with Laravel, Eloquent & TailwindCSS.</p>
                </div>
            </div>

            <div class="w-full mt-4 mx-auto py-4 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-center gap-4">
                <p class="text-xs font-medium text-gray-400 order-2 sm:order-1">&copy; {{ date('Y') }} DevHub Platform. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <div x-data="{ show: false, message: ''}"
        x-on:live-notification.window="show = true; message = $event.detail.message; setTimeout(() => show = false, 5000)"
        x-show="show"
        class="fixed top-5 right-5 bg-white border cursor-pointer border-gray-100 shadow-2xl rounded-xl p-4 z-50 max-w-sm flex items-start gap-3 glass-effect"
        style="display: none;">
        <div class="bg-gray-900 text-white p-2 rounded-lg shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
        </div>
        <div class="flex-1">
            <h4 class="text-sm font-bold text-gray-900">New Notification</h4>
            <p class="text-sm text-gray-600 mt-0.5" x-text="message"></p>
        </div>
    </div>

    <script>
        window.onload = function () {
            const userId = "{{ auth()->id() }}";
            if (userId && window.Echo) {
                window.Echo.private(`App.Models.User.${userId}`)
                    .notification((notification) => {
                        window.dispatchEvent(new CustomEvent('live-notification', {
                            detail: { message: notification.message,}
                        }));
                    });
            }
        };
    </script>
    @livewireScripts
</body>
</html>