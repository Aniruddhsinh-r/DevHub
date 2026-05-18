<x-layout>
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-6 m-2">
        <div>
            <h1 class="mt-2 text-5xl font-black tracking-tight text-gray-900">Latest Articles</h1>
            <p class="mt-4 text-sm text-gray-500 font-medium max-w-lg leading-relaxed">Explore fresh perspectives and deep dives from our community of thinkers.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-8 mt-10">
        @include('components.articleLayout')
    </div>
</section>
</x-layout>
