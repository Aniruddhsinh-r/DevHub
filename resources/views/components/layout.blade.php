<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    {{-- <script src="https://cdn.tailwindcss.com"></script> --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.10/dist/cdn.min.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

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
                        <a href="{{ route('home') }}" class="text-2xl font-bold tracking-tighter italic">DevHub</a>
                    </div>

                    <div class="flex items-center gap-4 md:gap-8">
                        @if (auth()->user()?->role === 'admin')
                            <a href="/admin/dashboard" class="hidden md:block text-sm font-medium hover:text-gray-500 transition">dashboard</a>
                        @else
                            <a href="{{ route('home')  }}" class="hidden md:block text-sm font-medium hover:text-gray-500 transition">home</a>
                            <a href="{{ route('show.articles')  }}" class="hidden md:block text-sm font-medium hover:text-gray-500 transition">articles</a>
                        @endif

                        @auth
                            @if (auth()->user()?->role === 'author')
                                <a href="{{ route('profile.index') }}" class="hidden md:block text-sm font-medium hover:text-gray-500 transition">profile</a>
                                <a href="{{ route('articles.create') }}" class="hidden sm:inline-flex items-center px-5 py-2.5 text-sm font-semibold text-white bg-[#1a1a1a] rounded-full hover:bg-gray-800 transition-all duration-300 shadow-sm">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                    </svg>Create Article
                                </a>
                            @endif

                            <div x-data="{ open: false }" data-test="Authbutton" class="relative z-50">
                                <button @click="open = !open" @click.outside="open = false" class="flex items-center focus:outline-none">
                                    {{-- @if (auth()->user()->avtar)
                                        <img src="{{ asset('storage/' . auth()->user()->avtar) }}" alt="User Profile" class="w-10 h-10 rounded-full object-cover border-2 border-gray-200 hover:border-gray-400 transition">
                                    @else --}}
                                        <span class="w-10 h-10 rounded-full bg-black text-white border border-gray-400 inline-flex items-center justify-center text-sm font-bold uppercase">
                                            {{ Str::upper(Str::substr(auth()->user()->name, 0, 2)) }}
                                        </span>
                                    {{-- @endif --}}
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
                                    @if (auth()->user()?->role === 'author')
                                        <a href="{{ route('show.bookmarks',auth()->user()->id) }}" class="w-full flex items-center gap-3 px-5 py-2 text-sm text-gray-700 font-medium hover:text-gray-500 transition">
                                            <svg class="w-5 h-5 text-gray-800" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                                            </svg>Bookmark
                                        </a>
                                    @endif

                                    <form action="{{ route('logout') }}" method="POST" class="w-full">
                                        @csrf
                                        <button type="submit" data-test="logout" class="w-full flex items-center gap-3 px-5 py-2 text-sm text-gray-700 font-medium hover:text-gray-500 transition">
                                            <svg class="w-5 h-5 text-gray-800" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                            </svg>Logout</button>
                                    </form>
                                </div>
                            </div>
                        @endauth

                        @guest
                            <a href="{{ route('register.create') }}" class="hidden md:block text-sm font-medium hover:text-gray-500 transition">
                                Sign Up
                            </a>
                            <a href="{{ route('login') }}" class="inline-flex items-center px-5 py-2.5 text-sm font-semibold text-white bg-[#1a1a1a] rounded-full hover:bg-gray-800 transition-all duration-300 shadow-sm">
                                Sign In
                            </a>
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
                 x-transition:enter-start="opacity-0 -translate-y-4"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 -translate-y-4"
                 class="md:hidden border-b border-gray-200 bg-white/95 backdrop-blur-lg absolute left-0 right-0 top-16 shadow-lg py-4 px-6 flex flex-col gap-4 z-40"
                 style="display: none;">

                @if (auth()->user()?->role === 'admin')
                    <a href="{{ route('admin.dashboard')  }}" class="text-base font-semibold hover:text-gray-500 transition py-2 border-b border-gray-50">dashboard</a>
                @else
                    <a href="{{ route('home')  }}" class="text-base font-semibold hover:text-gray-500 transition py-2 border-b border-gray-50">home</a>
                    <a href="{{ route('show.articles')  }}" class="text-base font-semibold hover:text-gray-500 transition py-2 border-b border-gray-50">articles</a>
                @endif

                @auth
                    @if (auth()->user()?->role === 'author')
                        <a href="{{ route('profile.index') }}" class="text-base font-semibold hover:text-gray-500 transition py-2 border-b border-gray-50">profile</a>
                        <a href="/articles/create" class="sm:hidden inline-flex items-center justify-center w-full px-5 py-2.5 text-sm font-semibold text-white bg-[#1a1a1a] rounded-full hover:bg-gray-800 transition shadow-sm mt-2">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>Create Article
                        </a>
                    @endif
                @endauth

                @guest
                    <a href="{{ route('register.create') }}" class="text-base font-semibold hover:text-gray-500 transition py-2">Sign Up</a>
                @endguest
            </div>
        </nav>

        <div class="text-black">
            {{ $slot }}
        </div>

        @if (session()->has('success'))
            <div data-test="success-message" class="hidden">
                {{ session('success') }}
            </div>

            <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 4000)" x-show="show" x-transition
                class="fixed top-5 right-5 bg-green-600 text-white px-6 py-3 rounded-md shadow-lg z-50 flex items-center space-x-3">

                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 8" />
                </svg>

                <span class="text-sm font-medium tracking-wide">{{ session('success') }}</span>
            </div>
        @endif

        @if (session()->has('error'))
            <div data-test="error-message" class="hidden">
                {{ session('error') }}
            </div>

            <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 4000)" x-show="show" x-transition
                class="fixed top-5 right-5 bg-red-600 text-white px-6 py-3 rounded-md shadow-lg z-50 flex items-center space-x-3">

                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>

                <span class="text-sm font-medium tracking-wide">{{ session('error') }}</span>
            </div>
        @endif
    </main>

    @if (auth()->user()?->role !== 'admin')
        @include('components.footer')
    @endif

</body>
</html>
