@foreach($articles as $article)
        <a href="{{ route('articles.show', $article) }}" class="group bg-white rounded-[20px] overflow-hidden border border-gray-200 shadow-sm hover:shadow-2xl hover:-translate-y-1 transition-all duration-500 cursor-pointer">
            <div class="relative overflow-hidden h-52 bg-gray-300">
                @if ($article->cover_path)
                    <img src="{{ asset('storage/' . $article->cover_path) }}" alt="{{ $article->title }}" class="w-full h-52 object-cover group-hover:scale-105 transition-transform duration-700">
                @endif
                <div class="absolute top-4 right-4 bg-black/75 backdrop-blur-md text-white text-xs font-semibold px-4 py-2 rounded-full">{{ $article->view_count }} Views</div>
            </div>

            <div class="p-5">
                    <div class="flex items-center gap-3 mb-3">
                        @if ($article->user->avatar)
                            <img src="{{ asset('storage/' . $article->user->avatar) }}" alt="user_image" class="w-9 h-9 rounded-full border-2 border-black object-cover">
                        @else
                            <div class="w-9 h-9 rounded-full bg-black text-white flex items-center justify-center text-sm font-black uppercase shrink-0">{{ substr($article->user->name, 0, 1) }}</div>
                        @endif
                        <div>
                            <h3 class="text-sm font-bold text-gray-900">{{ $article->user->name }}</h3>
                            <p class="text-xs text-gray-400 font-medium">{{ $article->category->name }}</p>
                        </div>
                    </div>

                <h3 class="text-xl line-clamp-1 font-black leading-tight tracking-tight text-gray-800">{{ $article->title }}</h3>
                <p class="mt-2 h-10 text-gray-600 text-sm leading-relaxed line-clamp-2">{{ $article->excerpt }}</p>

                <div class="mt-5 flex items-center justify-between border-t border-gray-50">
                    <div>
                        <p class="text-[10px] uppercase tracking-[0.18em] font-bold text-gray-400">{{ $article->status}}</p>
                        <p class="text-xs font-semibold text-gray-700 mt-0.5">{{ $article->created_at->diffForHumans() }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-[10px] uppercase tracking-[0.18em] font-bold text-gray-400">Date</p>
                        <p class="text-xs font-semibold text-gray-700 mt-0.5">{{ $article->created_at->format('d M Y') }}</p>
                    </div>
                </div>
            </div>
        </a>
        @endforeach

