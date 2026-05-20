<x-admin>
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <span class="text-[11px] font-black uppercase tracking-[0.25em] text-indigo-500">Content Management</span>
                <h1 class="text-2xl font-black text-[#111827] mt-2 tracking-tight">Platform Articles</h1>
            </div>

            <div class="flex items-center gap-3">
                <div class="bg-white border border-gray-200 rounded-2xl px-4 py-2 shadow-sm">
                    <span class="text-xs text-gray-400 font-bold uppercase tracking-wider block">Total Articles</span>
                    <span class="text-lg font-black text-[#111827]">{{ $articles->count() }}</span>
                </div>
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-[2rem] shadow-sm overflow-hidden">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 px-5 py-5 border-b border-gray-100">
                <div>
                    <h2 class="text-lg font-black text-[#111827] tracking-tight">All Written Articles</h2>
                    <p class="text-sm text-gray-500 mt-1">Manage, draft, review, and delete your site content here.</p>
                </div>
                <div class="relative w-full lg:w-[280px]">
                    <input type="text" placeholder="Search articles..." class="w-full h-11 rounded-2xl border border-gray-200 bg-[#fafafa] pl-4 pr-4 text-sm font-medium text-gray-700 outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition">
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[850px]">
                    <thead class="bg-[#fafafa] border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-4 text-left text-[11px] font-black uppercase tracking-[0.2em] text-gray-400">Article Details</th>
                            <th class="px-6 py-4 text-left text-[11px] font-black uppercase tracking-[0.2em] text-gray-400">Auther</th>
                            <th class="px-6 py-4 text-left text-[11px] font-black uppercase tracking-[0.2em] text-gray-400">category</th>
                            <th class="px-6 py-4 text-left text-[11px] font-black uppercase tracking-[0.2em] text-gray-400">Published</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100">
                        @foreach ($articles as $article)
                            <tr class="hover:bg-gray-50/70 transition-all duration-200">
                                <td class="px-6 py-4">
                                    <div class="min-w-0 max-w-[400px]">
                                        <h3 class="text-sm font-bold text-[#111827] truncate">
                                            {{ $article->title }}
                                        </h3>
                                        <p class="text-xs text-gray-500 truncate mt-0.5">
                                            {{ Str::limit($article->body ?? $article->content ?? '', 75) }}
                                        </p>
                                    </div>
                                </td>

                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <img src="{{ asset('storage/' . $article->user->avtar) }}" class="w-9 h-9 rounded-xl object-cover border border-gray-200">
                                        <div>
                                            <h4 class="text-sm font-bold text-[#111827] line-clamp-1">
                                                {{ $article->user->name }}
                                            </h4>
                                            <p class="text-xs text-gray-500 line-clamp-1">
                                                {{ $article->user->email }}
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-[11px] font-bold text-gray-600">
                                        {{ $article->category->name ?? 'Article' }}
                                    </span>
                                </td>

                                <td class="px-6 py-4 text-sm font-medium text-gray-500">
                                    {{ \Carbon\Carbon::parse($article->created_at)->format('d M Y') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if(method_exists($articles, 'links'))
                <div class="px-6 py-4 bg-[#fafafa] border-t border-gray-100">
                    {{ $articles->links() }}
                </div>
            @endif
        </div>
    </div>
</x-admin>
105
{{-- <x-admin>
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
</x-admin> --}}
