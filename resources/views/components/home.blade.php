<x-layout>
    <!-- Professional Slate & Charcoal Hero Section -->
<header class="w-full bg-[#3b3f44] flex items-center justify-center px-4 md:px-10 py-12 border-b border-white/5">
    <div class="max-w-5xl w-full flex flex-col-reverse md:flex-row items-center justify-between gap-8">

        <!-- Left Section: Content -->
        <div class="w-full md:w-[55%] text-center md:text-left">
            <div class="inline-block px-3 py-1 mb-6 text-[9px] font-bold tracking-[0.2em] uppercase bg-white/10 text-gray-300 border border-white/10 rounded backdrop-blur-sm">
                Editorial Collection 2026
            </div>

            <h1 class="text-4xl md:text-5xl lg:text-6xl font-black text-white leading-[1.1] tracking-tighter">
                WRITE. READ.<br>
                <span class="text-gray-500">REPEAT.</span>
            </h1>

            <p class="text-gray-400 mt-4 text-base md:text-lg font-light max-w-sm leading-relaxed">
                A minimalist sanctuary for <span class="text-white font-normal">profound ideas</span> and community-driven stories.
            </p>

            <div class="mt-8 w-full max-w-md">
                <form action="/articles/search" method="get" class="flex items-center bg-[#1c1f24] border border-white/10 rounded-xl overflow-hidden focus-within:border-gray-500 transition-all shadow-2xl">
                    <input type="text" name="search" placeholder="Search archives..." class="flex-1 px-5 py-3.5 outline-none text-gray-200 text-sm font-light bg-transparent">
                    <button class="bg-white text-black px-6 py-3.5 text-sm font-bold hover:bg-gray-200 transition-all active:scale-95">
                        Search
                    </button>
                </form>
            </div>
        </div>

        <!-- Right Section: Compact Image -->
        <div class="w-full md:w-[40%] flex justify-center md:justify-end">
            <div class="relative group">
                <!-- Decorative dark frame -->
                <div class="absolute -inset-3 border border-white/5 rounded-2xl -z-10 transition-transform group-hover:scale-105 duration-500"></div>

                <!-- The Image -->
                <img src="https://images.unsplash.com/photo-1488190211105-8b0e65b80b4e?auto=format&fit=crop&q=80&w=600"
                     alt="Writer working"
                     class="w-52 md:w-60 lg:w-72 h-72 md:h-80 object-cover rounded-2xl grayscale brightness-90 group-hover:grayscale-0 group-hover:brightness-100 transition-all duration-700 shadow-2xl border border-white/10"
                />

                <!-- Floating Detail -->
                <div class="absolute -top-4 -right-4 bg-[#1c1f24] border border-white/10 text-white text-[10px] font-bold px-4 py-2 rounded-lg shadow-xl tracking-tighter">
                    NEW POSTS
                </div>
            </div>
        </div>
    </div>
</header>

<div class="grid grid-cols-1 mx-4 md:grid-cols-2 xl:grid-cols-3 gap-8 mt-10">
    @include('components.articleLayout')
</div>
<footer class="bg-white border-t border-gray-100 mt-20">
    <div class="max-w-7xl mx-auto px-4 py-12 sm:px-6 lg:px-8">
        <div class="flex flex-col gap-8 md:flex-row md:justify-between md:items-start">

            <div class="space-y-4 md:w-1/3">
                <a href="/" class="text-xl font-black tracking-tight text-black flex items-center gap-2">
                    <span>Dev<span class="text-indigo-600">Hub</span></span>
                </a>
                <p class="text-gray-500 text-sm max-w-sm font-medium leading-relaxed">
                    A token-space ecosystem for developers and creators to share insights, build followings, and discover top technical content.
                </p>
            </div>

            <div class="md:w-1/4">
                <h3 class="text-xs font-semibold text-slate-400 tracking-wider uppercase">
                    EXPLORE
                </h3>
                <ul role="list" class="mt-4 space-y-3">
                    <li>
                        <a href="/articles" class="text-sm font-bold text-slate-700 hover:text-black transition-colors duration-150">
                            Latest Articles
                        </a>
                    </li>
                    @auth
                        <li>
                            <a href="/profile/followings/{{ auth()->user()->id }}" class="text-sm font-bold text-slate-700 hover:text-black transition-colors duration-150">
                                Following List
                            </a>
                        </li>
                        <li>
                            <a href="/profile" class="text-sm font-bold text-slate-700 hover:text-black transition-colors duration-150">
                                Your Profile
                            </a>
                        </li>
                    @else
                        <li>
                            <a href="/login" class="text-sm font-bold text-slate-700 hover:text-black transition-colors duration-150">
                                Sign In
                            </a>
                        </li>
                        <li>
                            <a href="/register" class="text-sm font-bold text-slate-700 hover:text-black transition-colors duration-150">
                                Create Account
                            </a>
                        </li>
                    @endauth
                </ul>
            </div>

            <div class="md:w-1/4">
                <h3 class="text-xs font-semibold text-slate-400 tracking-wider uppercase">
                    PLATFORM INFO
                </h3>
                <p class="mt-4 text-sm font-medium text-slate-400 leading-relaxed">
                    Built with Laravel, Eloquent & TailwindCSS.
                </p>
            </div>

        </div>

        <div class="mt-12 pt-8 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-4">
            <p class="text-xs font-medium text-gray-400 order-2 sm:order-1">
                &copy; {{ date('Y') }} DevHub Platform. All rights reserved.
            </p>

            <div class="flex items-center gap-2 order-1 sm:order-2">
                <span class="flex h-2 w-2 relative">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                </span>
                <span class="text-xs font-bold uppercase tracking-wider text-gray-400">Systems Operational</span>
            </div>
        </div>
    </div>
</footer>
</x-layout>
