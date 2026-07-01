<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <span class="text-[11px] font-black uppercase tracking-[0.25em] text-indigo-500">Content Management</span>
            <h1 class="text-2xl font-black text-[#111827] mt-2 tracking-tight">Platform Articles</h1>
        </div>

        <div class="flex items-center gap-3">
            @if(count($selectedArticles) > 0)
                <button x-on:click="$dispatch('open-delete', { id: {{ json_encode($selectedArticles) }}, title: '{{ count($selectedArticles) }} selected articles', type: 'adminArticleBulkDelete'})"
                     class="inline-flex items-center gap-2 h-[52px] px-5 rounded-2xl bg-red-600 hover:bg-red-700 text-sm font-black text-white shadow-sm hover:shadow-lg transition-all duration-200 animate-fade-in">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Delete Selected ({{ count($selectedArticles) }})
                </button>
            @endif
            <a href="{{ route('admin.articles.create') }}" wire:navigate class="inline-flex items-center gap-2 h-[52px] px-5 rounded-2xl bg-[#111827] hover:bg-gray-800 text-sm font-black text-white shadow-sm hover:shadow-lg transition-all duration-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                Create Article
            </a>
            <div class="bg-white border border-gray-200 rounded-2xl px-4 py-2 shadow-sm">
                <span class="text-xs text-gray-400 font-bold uppercase tracking-wider block">Total Articles</span>
                <span class="text-lg font-black text-[#111827]">{{ $articles->total() }}</span>
            </div>
        </div>
    </div>

    <div class="bg-white border border-gray-200 rounded-[2rem] shadow-sm overflow-hidden">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 px-6 py-5">
            <div>
                <h2 class="text-lg font-black text-[#111827] tracking-tight">All Written Articles</h2>
            </div>
            <div class="relative w-full lg:w-64">
                <form action="" method="GET">
                    <div class="relative">
                        <input type="text" wire:model.live="search" value="{{ request('search') }}" placeholder="Search articles..." class="w-full h-11 rounded-2xl border border-gray-200 bg-[#fafafa] pl-4 pr-4 text-sm font-medium text-gray-700 outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition">

                        @if (request('search'))
                            <a href="{{ url()->current() }}" wire:navigate class="absolute right-11 top-1/2 -translate-y-1/2 w-5 h-5 rounded-full bg-gray-200 hover:bg-black hover:text-white text-gray-600 flex items-center justify-center transition-all duration-200">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </a>
                        @endif

                        <button type="submit" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-black transition-all duration-200">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m1.85-5.15a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        @if($articles->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-gray-100 bg-[#fafafa]">
                            <th class="pl-6 py-4 text-xs font-black uppercase tracking-wider text-gray-400">
                                box
                            </th>
                            <th class="pr-6 py-4 text-xs font-black uppercase tracking-wider text-gray-400">Article Title</th>
                            <th class="px-6 py-4 text-xs font-black uppercase tracking-wider text-gray-400">Status</th>
                            <th class="px-6 py-4 text-xs font-black uppercase tracking-wider text-gray-400">Published Time</th>
                            <th class="px-6 py-4 text-xs font-black uppercase tracking-wider text-gray-400 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($articles as $article)
                            <tr x-data x-on:click="Livewire.navigate('{{ route('admin.article.show', $article) }}')" class="hover:bg-gray-50/70 transition-all cursor-pointer group">
                                <td class="px-6 py-4" x-on:click.stop>
                                    <input type="checkbox" wire:model.live="selectedArticles" value="{{ $article->id }}" class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                </td>
                                <td class="pr-6 py-4">
                                    <div class="max-w-md">
                                        <div class="text-sm font-bold text-gray-900 line-clamp-1 group-hover:text-indigo-600 transition-colors">{{ $article->title }}</div>
                                        <div class="text-xs text-gray-500 line-clamp-1 mt-0.5">{{ $article->excerpt }}</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-gray-100 text-gray-800">
                                        {{ $article->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm font-semibold text-gray-600 whitespace-nowrap">
                                    {{ $article->created_at->diffForHumans() }}
                                </td>
                                <td class="px-6 py-4 text-right whitespace-nowrap" x-on:click.stop>
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.articles.edit', $article) }}" wire:navigate class="inline-flex items-center justify-center bg-gray-100 hover:bg-[#111827] text-[#111827] hover:text-white font-bold text-xs px-3.5 py-2 rounded-xl transition-all duration-150">
                                            Edit
                                        </a>
                                        <button type="button" x-on:click="$dispatch('open-delete', { id: {{ $article->id }}, title: '{{ addslashes($article->title) }}', type: 'adminArticleDelete' })"
                                            class="inline-flex items-center justify-center bg-red-50 hover:bg-red-600 text-red-600 hover:text-white font-bold text-xs px-3.5 py-2 rounded-xl transition-all duration-150">
                                            Remove
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 border-t border-gray-100 font-semibold">
                {{ $articles->appends(['search' => request('search')])->links() }}
            </div>
        @else
            @if(filled($search))
            <div class="flex flex-col items-center justify-center p-12 text-center border-2 border-dashed border-gray-200 rounded-[2rem] m-4 bg-[#fafafa]">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>

                <h3 class="text-md font-black text-gray-700">No Matches Found</h3>

                <p class="text-sm text-gray-500 mt-1 max-w-xs">
                    We couldn't find anything matching <span class="font-semibold text-gray-800">"{{ $search }}"</span>. Try checking your spelling or using different keywords.
                </p>

                <div class="mt-5">
                    <button type="button" wire:click="$set('search', '')"
                            class="inline-flex items-center gap-2 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold text-xs px-5 py-2.5 rounded-xl transition-all shadow-sm">
                        Clear Search
                    </button>
                </div>
            </div>
            @else
            <div class="flex flex-col items-center justify-center p-12 text-center border-2 border-dashed border-gray-200 rounded-2xl m-6 bg-[#fafafa]">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                </svg>
                <h3 class="text-md font-black text-gray-700">No Articles Found</h3>
                <p class="text-sm text-gray-500 mt-1 max-w-xs">We couldn't find any written entries. Try adjusting your search query or create a fresh article.</p>
            </div>
            @endif
        @endif
    </div>
</div>
