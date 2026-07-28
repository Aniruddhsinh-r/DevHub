<x-filament-widgets::widget>
    <x-filament::section heading="Latest Articles">

        @if($this->articles->isNotEmpty())
            <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
                @foreach ($this->articles as $article)
                    <div class="group relative bg-white dark:bg-gray-900 rounded-xl border border-gray-200/80 dark:border-gray-800 shadow-sm hover:shadow-md hover:border-emerald-500/50 dark:hover:border-emerald-500/50 transition-all duration-200 flex flex-col sm:flex-row overflow-hidden">
                        <a href="/app/articles/{{ $article->slug }}/edit" wire:navigate class="absolute inset-0 z-10" aria-label="{{ $article->title }}"></a>
                        <div class="sm:w-2/5 shrink-0 relative overflow-hidden bg-gray-100 dark:bg-gray-800 min-h-[160px]">
                            @if ($article->cover_path)
                                <img src="{{ asset('storage/' . $article->cover_path) }}" alt="{{ $article->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300 ease-out">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-gray-400 dark:text-gray-600"><x-heroicon-o-document-text class="w-8 h-8" /></div>
                            @endif
                            <div class="absolute top-2.5 left-2.5 bg-black/60 backdrop-blur-md text-white text-[10px] font-medium px-2.5 py-1 rounded-md pointer-events-none">{{ number_format($article->view_count) }} views</div>
                        </div>

                        <div class="p-4 sm:w-3/5 flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between gap-2 mb-2">
                                    <div class="flex items-center gap-2 min-w-0">
                                        @if ($article->user->avatar)
                                            <img src="{{ asset('storage/' . $article->user->avatar) }}" alt="{{ $article->user->name }}" class="w-6 h-6 rounded-full ring-1 ring-gray-900/10 dark:ring-white/20 object-cover">
                                        @else
                                            <div class="w-6 h-6 rounded-full bg-gray-900 dark:bg-white text-white dark:text-gray-900 flex items-center justify-center text-[10px] font-bold uppercase shrink-0">
                                                {{ substr($article->user->name, 0, 1) }}
                                            </div>
                                        @endif
                                        <span class="text-xs font-semibold text-gray-900 dark:text-gray-200 truncate">{{ $article->user->name }}</span>
                                    </div>
                                    <span class="text-xs font-medium text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 px-2.5 py-1 rounded shrink-0">
                                        {{ $article->category->name }}
                                    </span>
                                </div>

                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors line-clamp-1">
                                    {{ $article->title }}
                                </h3>
                                <p class="mt-1 text-gray-500 dark:text-gray-400 line-clamp-2 leading-relaxed">
                                    {{ $article->excerpt }}
                                </p>
                            </div>

                            <div class="mt-2 pt-2 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between text-xs text-gray-400 dark:text-gray-500">
                                <div>
                                    <span class="font-medium text-gray-600 dark:text-gray-400">
                                        {{ $article->published_at?->diffForHumans() ?? 'Draft' }}
                                    </span>
                                </div>

                                <div>
                                    @if(auth()->id() === $article->user_id)
                                        <a href="/app/articles/{{ $article->slug }}/edit" wire:navigate
                                           class="relative z-20 inline-flex items-center text-xs font-semibold text-emerald-500 dark:text-emerald-400 border border-emerald-500/40 bg-emerald-400/10 hover:bg-emerald-400 hover:text-white dark:hover:text-black px-2.5 py-1 rounded-lg transition-all">Edit</a>
                                    @else
                                        <span>{{ $article->created_at->format('M d, Y') }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <x-filament::empty-state heading="No articles found" description="There are no published articles yet."/>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>