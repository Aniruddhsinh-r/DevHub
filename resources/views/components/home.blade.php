<x-layout>
<header class="w-full bg-[#3b3f44] flex items-center justify-center px-4 md:px-10 py-12 border-b border-white/5">
    <div class="max-w-5xl w-full flex flex-col-reverse md:flex-row items-center justify-between gap-8">

        <div class="w-full md:w-[55%] text-center md:text-left">
            <div class="inline-block px-3 py-1 mb-6 text-[9px] font-bold tracking-[0.2em] uppercase bg-white/10 text-gray-300 border border-white/10 rounded backdrop-blur-sm">
                Editorial Collection 2026
            </div>

            <h1 class="text-4xl md:text-5xl lg:text-6xl font-black text-white leading-[1.1] tracking-tighter"> WRITE. READ.<br>
                <span class="text-gray-500">REPEAT.</span>
            </h1>

            <p class="text-gray-400 mt-4 text-base md:text-lg font-light max-w-sm leading-relaxed">
                A minimalist sanctuary for <span class="text-white font-normal">profound ideas</span> and community-driven stories.
            </p>

            <div class="mt-8 w-full max-w-md">
                <form action="{{ route('articles.index') }}" method="get" class="flex items-center bg-[#1c1f24] border border-white/10 rounded-xl overflow-hidden focus-within:border-gray-500 transition-all shadow-2xl">
                    <input type="text" name="search" placeholder="Search archives..." class="flex-1 px-5 py-3.5 outline-none text-gray-200 text-sm font-light bg-transparent">
                    <button class="bg-white text-black px-6 py-3.5 text-sm font-bold hover:bg-gray-200 transition-all active:scale-95">
                        Search
                    </button>
                </form>
            </div>
        </div>

        <div class="w-full md:w-[40%] flex justify-center md:justify-end">
            <div class="relative group">
                <div class="absolute -inset-3 border border-white/5 rounded-2xl -z-10 transition-transform group-hover:scale-105 duration-500"></div>

                <img src="https://images.unsplash.com/photo-1488190211105-8b0e65b80b4e?auto=format&fit=crop&q=80&w=600"
                     alt="Writer working"
                     class="w-52 md:w-60 lg:w-72 h-72 md:h-80 object-cover rounded-2xl grayscale brightness-90 group-hover:grayscale-0 group-hover:brightness-100 transition-all duration-700 shadow-2xl border border-white/10"
                />

                <div class="absolute -top-4 -right-4 bg-[#1c1f24] border border-white/10 text-white text-[10px] font-bold px-4 py-2 rounded-lg shadow-xl tracking-tighter">
                    NEW POSTS
                </div>
            </div>
        </div>
    </div>
</header>

<div class="grid grid-cols-1 mx-4 md:grid-cols-2 xl:grid-cols-3 gap-8 my-10">
    @include('components.articleLayout')
</div>
</x-layout>
