<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.10/dist/cdn.min.js"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .glass-effect { background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(10px); }
    </style>
</head>
<body class="bg-[#f8f9fa] text-[#1a1a1a]">
    <main>
        <nav class="sticky top-0 z-50 border-b border-gray-200 glass-effect">
            <div class="max-w-6xl mx-auto px-4 sm:px-2">
                <div class="flex justify-between items-center h-16">

                    <div class="flex items-center">
                        <a href="/" class="text-2xl font-bold tracking-tighter italic">
                            DevHub
                        </a>
                    </div>

                    <div class="flex items-center gap-8">
                        @auth
                            <a href="/home" class="hidden md:block text-sm font-medium hover:text-gray-500 transition">home</a>
                            {{-- <a href="/about" class="hidden md:block text-sm font-medium hover:text-gray-500 transition">about</a> --}}
                            <a href="/profile" class="hidden md:block text-sm font-medium hover:text-gray-500 transition">profile</a>
                            <a href="/articles" class="hidden md:block text-sm font-medium hover:text-gray-500 transition">article</a>

                            <a href="/articles/create"
                                class="inline-flex items-center px-5 py-2.5 text-sm font-semibold text-white bg-[#1a1a1a] rounded-full hover:bg-gray-800 transition-all duration-300 shadow-sm">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4">
                                    </path>
                                </svg>Create Article
                            </a>
                        @endauth

                            <form action="/logout" method="POST">
                                @csrf
                                <button type="submit" class="text-sm font-medium hover:text-gray-500 transition">
                                    Logout
                                </button>
                            </form>

                        @guest
                            <a href="/register" class="hidden md:block text-sm font-medium hover:text-gray-500 transition">
                                Sign Up
                            </a>
                            <a href="/login"
                                class="inline-flex items-center px-5 py-2.5 text-sm font-semibold text-white bg-[#1a1a1a] rounded-full hover:bg-gray-800 transition-all duration-300 shadow-sm">
                                Sign In
                            </a>
                        @endguest
                    </div>
                </div>
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
</body>
</html>
