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
<body class="bg-[#f8f9fa] text-[#1a1a1a]">
<main>
    <nav x-data="{ mobileMenuOpen: false }" class="sticky top-0 z-50 border-b border-gray-200 glass-effect">
        <div class="max-w-6xl mx-auto px-4 sm:px-2">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <a href="{{ route('admin.dashboard') }}" class="text-2xl font-bold tracking-tighter italic">DevHub</a>
                </div>
                <div class="flex items-center gap-4 md:gap-8">
                    @role('admin')
                        <a href="{{ route('admin.dashboard') }}" class="hidden md:block text-sm font-medium hover:text-gray-500 transition">dashboard</a>

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

                        <button @click="mobileMenuOpen = !mobileMenuOpen" class="block md:hidden text-[#1a1a1a] focus:outline-none z-50">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path x-show="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                                <path x-show="mobileMenuOpen" x-cloak stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    @endrole
                </div>
            </div>
        </div>

        <div x-show="mobileMenuOpen"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-4"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-4"
            class="md:hidden border-b border-gray-200 bg-white/95 backdrop-blur-lg absolute left-0 right-0 top-16 shadow-lg py-4 px-6 flex flex-col gap-4 z-40"
            style="display: none;">
            @role('admin')
                <a href="{{ route('admin.dashboard') }}" class="text-base font-semibold hover:text-gray-500 transition py-2 border-b border-gray-50">dashboard</a>
            @endrole
        </div>
    </nav>

    <div class="flex overflow-hidden bg-[#f3f4f6] text-[#111827]" style="height: calc(100vh - 65px);">
        <aside class="w-14 sm:w-20 md:w-56 sticky top-16 bg-[#e5e7eb] border-r border-gray-300 flex flex-col justify-between p-2 sm:p-4 transition-all duration-300 shrink-0">
            <div>
                <div class="hidden md:flex items-center gap-3 mb-5 border-b pb-4 border-gray-300">
                    <div class="min-w-0 flex-1">
                        <span class="text-sm font-semibold text-gray-900 leading-none">{{ auth()->user()->name }}</span>
                        <span class="text-xs text-gray-500 block mt-0.5">{{ auth()->user()->email }}</span>
                    </div>
                </div>

                <nav class="flex flex-col gap-1">
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 p-2.5 rounded-lg cursor-pointer transition-all duration-200 md:justify-start sm:justify-center justify-center {{ request()->is('admin/dashboard') ? 'bg-white text-gray-900 font-semibold shadow-sm' : 'text-gray-600 hover:bg-white/50 hover:text-gray-900 font-medium' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24" class="w-5 h-5 text-gray-800">
                            <path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/>
                        </svg>
                        <span class="text-sm md:block sm:hidden hidden">Dashboard</span>
                    </a>

                    <a href="{{ route('admin.users') }}" class="flex items-center gap-3 p-2.5 rounded-lg cursor-pointer transition-all duration-200 md:justify-start sm:justify-center justify-center {{ request()->is('admin/user*') ? 'bg-white text-gray-900 font-semibold shadow-sm' : 'text-gray-600 hover:bg-white/50 hover:text-gray-900 font-medium' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5 text-gray-500">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                        </svg>
                        <span class="text-sm md:block sm:hidden hidden">Users</span>
                    </a>

                    <a href="{{ route('admin.articles') }}" class="flex items-center gap-3 p-2.5 rounded-lg cursor-pointer transition-all duration-200 md:justify-start sm:justify-center justify-center {{ request()->is('admin/articles*') ? 'bg-white text-gray-900 font-semibold shadow-sm' : 'text-gray-600 hover:bg-white/50 hover:text-gray-900 font-medium' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5 text-gray-500">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                        </svg>
                        <span class="text-sm md:block sm:hidden hidden">Articles</span>
                    </a>

                    <a href="{{ route('admin.categories') }}" class="flex items-center gap-3 p-2.5 rounded-lg cursor-pointer transition-all duration-200 md:justify-start sm:justify-center justify-center {{ request()->is('admin/categories') ? 'bg-white text-gray-900 font-semibold shadow-sm' : 'text-gray-600 hover:bg-white/50 hover:text-gray-900 font-medium' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5 text-gray-500">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z" />
                        </svg>
                        <span class="text-sm md:block sm:hidden hidden">Categories</span>
                    </a>
                </nav>
            </div>
        </aside>

        <div class="flex-1 overflow-y-auto p-4">
            {{ $slot }}
        </div>

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
    </div>
</main>

    <div x-data="{ show: false, message: ''}"
        x-on:live-notification.window="show = true; message = $event.detail.message; setTimeout(() => show = false, 5000)"
        x-show="show"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:translate-x-4"
        x-transition:enter-end="opacity-100 translate-y-0 sm:translate-x-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
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
        <button @click="show = false" class="text-gray-400 hover:text-gray-600 transition focus:outline-none">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
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
