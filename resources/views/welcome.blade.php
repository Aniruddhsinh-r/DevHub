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

            <!-- Modern Dark Search Bar -->
            <div class="mt-8 w-full max-w-md">
                <div class="flex items-center bg-[#1c1f24] border border-white/10 rounded-xl overflow-hidden focus-within:border-gray-500 transition-all shadow-2xl">
                    <input type="text"
                           placeholder="Search archives..."
                           class="flex-1 px-5 py-3.5 outline-none text-gray-200 text-sm font-light bg-transparent"
                    >
                    <button class="bg-white text-black px-6 py-3.5 text-sm font-bold hover:bg-gray-200 transition-all active:scale-95">
                        Search
                    </button>
                </div>
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

    <!-- Main Feed -->
    <main class="max-w-6xl mx-auto px-4 py-12">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-12">

            <!-- Article Card 1 -->
            <article class="group cursor-pointer">
                <div class="overflow-hidden rounded-sm bg-gray-100 mb-4 aspect-video">
                    <img src="https://images.unsplash.com/photo-1499750310107-5fef28a66643?auto=format&fit=crop&q=80&w=800"
                         alt="Cover" class="w-full h-full object-cover grayscale group-hover:grayscale-0 transition-all duration-500 transform group-hover:scale-105">
                </div>
                <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-gray-400 mb-2">
                    <span>Design</span> • <span>5 min read</span>
                </div>
                <h2 class="text-2xl font-bold leading-snug group-hover:underline underline-offset-4 decoration-1">
                    The intersection of minimalism and digital architecture.
                </h2>
                <p class="mt-3 text-gray-600 line-clamp-2 font-light">
                    How reducing the noise in UI can lead to higher user retention and better psychological health...
                </p>
                <div class="mt-6 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-black"></div>
                    <span class="text-sm font-medium">Alex Rivera</span>
                </div>
            </article>

            <!-- Article Card 2 -->
            <article class="group cursor-pointer">
                <div class="overflow-hidden rounded-sm bg-gray-100 mb-4 aspect-video">
                    <img src="https://images.unsplash.com/photo-1486312338219-ce68d2c6f44d?auto=format&fit=crop&q=80&w=800"
                         alt="Cover" class="w-full h-full object-cover grayscale group-hover:grayscale-0 transition-all duration-500 transform group-hover:scale-105">
                </div>
                <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-gray-400 mb-2">
                    <span>Technology</span> • <span>8 min read</span>
                </div>
                <h2 class="text-2xl font-bold leading-snug group-hover:underline underline-offset-4 decoration-1">
                    Why the future of AI belongs to the creators.
                </h2>
                <p class="mt-3 text-gray-600 line-clamp-2 font-light">
                    Artificial Intelligence is no longer a tool; it is a collaborator. Discover how to leverage it without losing your soul...
                </p>
                <div class="mt-6 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-gray-300"></div>
                    <span class="text-sm font-medium">Sarah Jenkins</span>
                </div>
            </article>

            <!-- Article Card 3 (Repeat for more) -->
            <article class="group cursor-pointer">
                <div class="overflow-hidden rounded-sm bg-gray-100 mb-4 aspect-video">
                    <img src="https://images.unsplash.com/photo-1516414447565-b14be0adf13e?auto=format&fit=crop&q=80&w=800"
                         alt="Cover" class="w-full h-full object-cover grayscale group-hover:grayscale-0 transition-all duration-500 transform group-hover:scale-105">
                </div>
                <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-gray-400 mb-2">
                    <span>Lifestyle</span> • <span>3 min read</span>
                </div>
                <h2 class="text-2xl font-bold leading-snug group-hover:underline underline-offset-4 decoration-1">
                    Morning routines of high-performance developers.
                </h2>
                <p class="mt-3 text-gray-600 line-clamp-2 font-light">
                    It isn't about the coffee. It's about the deep work block before the rest of the world wakes up...
                </p>
                <div class="mt-6 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-black"></div>
                    <span class="text-sm font-medium">Mark Voegel</span>
                </div>
            </article>

        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-100 py-10 mt-20">
        <div class="max-w-6xl mx-auto px-4 flex flex-col md:flex-row justify-between items-center gap-6">
            <p class="text-gray-400 text-sm">© 2026 The Journal. Built for thinkers.</p>
            <div class="flex gap-8 text-sm font-medium">
                <a href="#" class="hover:text-gray-400">Twitter</a>
                <a href="#" class="hover:text-gray-400">Newsletter</a>
                <a href="#" class="hover:text-gray-400">Privacy</a>
            </div>
        </div>
    </footer>
</x-layout>
