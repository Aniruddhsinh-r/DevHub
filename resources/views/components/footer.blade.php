    <footer class="bg-white border-t border-gray-100 mt-2">
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
