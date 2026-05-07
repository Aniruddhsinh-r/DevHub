<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    @vite(['resources/js/app.js'])
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@latest/dist/full.css" rel="stylesheet" />
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.10/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-100 h-screen m-0 p-0">
    <main class="">
        <div class="navbar bg-base-100 shadow-md">
            <div class="navbar-start">
                <div class="dropdown">
                    <div tabindex="0" role="button" class="btn btn-ghost lg:hidden">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h8m-8 6h16" />
                        </svg>
                    </div>
                    <ul tabindex="0"
                        class="menu menu-sm dropdown-content bg-white rounded-box z-1 mt-3 w-52 p-2 shadow">
                        <li><a href="/">Home</a></li>
                        <li><a href="/about">About</a></li>
                        <li><a href="/contact">Contact</a></li>
                        @can('view-admin')
                            <li><a href="/admin">Admin</a></li>
                        @endcan
                    </ul>
                </div>
                <a class="btn btn-ghost text-xl">DevHub</a>
            </div>

            <div class="navbar-center hidden lg:flex">
                <ul class="menu menu-horizontal px-1 gap-2">
                    <li><a href="/">Home</a></li>
                    <li><a href="/about">About</a></li>
                    <li><a href="/contact">Contact</a></li>
                    @can('view-admin')
                        <li><a href="/admin">Admin</a></li>
                    @endcan
                </ul>
            </div>

            <div class="navbar-end gap-2">
                @guest
                    <a href="/register" class="btn btn-sm">Sign up</a>
                    <a href="/login" class="btn btn-sm bg-blue-700 text-white">Sign in</a>
                @endguest
                @auth
                    <form action="/logout" method="post">
                        @csrf
                        <a href="/profile">Profile</a>
                        <button class="btn btn-sm bg-red-700 text-white" data-test="logout-btn">Logout</button>
                    </form>
                @endauth
            </div>
        </div>
        <div class="text-black">
            {{ $slot }}
        </div>
        @if (session()->has('success'))
            <div data-test="success-message" class="hidden">
                {{ session('success') }}
            </div>

            <div x-data="{ show: true }"
                 x-init="setTimeout(() => show = false, 4000)"
                 x-show="show"
                 x-transition
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

            <div x-data="{ show: true }"
                 x-init="setTimeout(() => show = false, 4000)"
                 x-show="show"
                 x-transition
                 class="fixed top-5 right-5 bg-red-600 text-white px-6 py-3 rounded-md shadow-lg z-50 flex items-center space-x-3">

                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>

                <span class="text-sm font-medium tracking-wide">{{ session('error') }}</span>
            </div>
        @endif
    </main>
</body>
</html>
