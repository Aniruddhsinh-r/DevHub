<x-layout>
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6">
        <div>
            <h1 class="text-5xl font-black tracking-tight text-gray-900">
                Latest Articles
            </h1>

            <p class="mt-4 text-sm text-gray-500 font-medium max-w-lg leading-relaxed">
                Explore fresh perspectives and deep dives from our community of thinkers.
            </p>
        </div>

        <div class="w-full max-w-md lg:ml-auto">
            <form action="/articles/search" method="get" class="flex items-center bg-[#1c1f24] border border-white/10 rounded-xl overflow-hidden focus-within:border-gray-500 transition-all shadow-2xl">
                <input type="text" name="search" placeholder="Search archives..." class="flex-1 px-5 py-3.5 outline-none text-gray-200 text-sm font-light bg-transparent">

                <button class="bg-white text-black px-6 py-3.5 text-sm font-bold hover:bg-gray-200 transition-all active:scale-95">Search</button>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-8 mt-10">
        @include('components.articleLayout')
    </div>

</section>
</x-layout>
