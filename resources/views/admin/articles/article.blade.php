<x-admin>
    <section>
        <div class="relative overflow-hidden bg-black">
            @if ($article->cover_path)
                <div class="absolute inset-0 opacity-20">
                    <img src="{{ asset('storage/' . $article->cover_path) }}" alt="{{ $article->title }}" class="w-full h-full object-cover">
                </div>
            @endif
            <div class="relative max-w-7xl mx-auto px-5 lg:px-8 py-10 lg:py-14">
                <div class="w-full flex flex-wrap items-center mb-5 justify-between">
                    <div class="flex gap-4">
                        <span class="bg-white/10 backdrop-blur-md border border-white/10 text-white px-4 py-1.5 rounded-full text-[11px] font-black uppercase tracking-[0.2em]">
                            {{ $article->category->name }}
                        </span>
                        <span class="bg-emerald-500 text-white px-4 py-1.5 rounded-full text-[11px] font-black uppercase tracking-[0.2em]">
                            {{ $article->status }}
                        </span>
                    </div>
                </div>

                <h1 class="text-white text-3xl md:text-4xl lg:text-5xl font-black leading-tight tracking-tight max-w-5xl">
                    {{ $article->title }}
                </h1>
                <p class="mt-5 text-gray-300 text-base md:text-lg leading-relaxed max-w-3xl font-medium">
                    {{ $article->excerpt }}
                </p>

                <div class="mt-7 flex flex-wrap items-center gap-5 text-white/80 text-sm font-semibold">
                    <a href="{{ route('admin.show.user',$article->user) }}" class="flex items-center gap-3">
                        @if ($article->user->avatar)
                            <img src="{{ asset('storage/' . $article->user->avatar) }}" alt="user_image" class="w-9 h-9 rounded-full border-2 border-black object-cover">
                        @else
                            <div class="w-9 h-9 rounded-full bg-black text-white flex items-center justify-center text-sm font-black uppercase shrink-0">{{ substr($article->user->name, 0, 1) }}</div>
                        @endif
                        <div>
                            <h3 class="text-sm font-bold text-white">{{ $article->user->name }}</h3>
                            <p class="text-xs text-gray-400 font-medium">{{ $article->category->name }}</p>
                        </div>
                    </a>
                    <div>
                        <p class="text-[10px] uppercase tracking-[0.2em] text-gray-500 font-black">Published</p>
                        <p class="mt-0.5 font-bold text-white text-sm">{{ $article->created_at->format('F d, Y') }}</p>
                    </div>
                    <div class="h-8 w-px bg-white/10 hidden md:block"></div>
                    <div>
                        <p class="text-[10px] uppercase tracking-[0.2em] text-gray-500 font-black">Views</p>
                        <p class="mt-0.5 font-bold text-white text-sm">{{ number_format($article->view_count) }} Views</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-5 lg:px-8 mt-10">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
                <div class="lg:col-span-8 space-y-10">
                    <article class="bg-white rounded-[2rem] shadow-xl shadow-gray-200/40 border border-gray-100 overflow-hidden">
                        <div class="p-6 md:p-10">
                            <div class="flex items-center gap-3 mb-10">
                                <span class="bg-black text-white px-4 py-2 rounded-full text-[10px] font-black uppercase tracking-[0.2em]">
                                    {{ $article->category->name }}
                                </span>
                                <span class="text-xs font-bold uppercase tracking-wide text-gray-400">
                                    {{ $article->created_at->diffForHumans() }}
                                </span>
                            </div>

                            <div class="prose prose-lg max-w-none prose-headings:font-black prose-headings:text-gray-900 prose-p:text-gray-700 prose-p:leading-9 prose-p:text-[17px] prose-img:rounded-3xl prose-a:text-black hover:prose-a:text-gray-600">
                                {!! nl2br(e($article->body)) !!}
                            </div>
                        </div>
                    </article>

                    <div class="bg-white rounded-[2rem] border border-gray-100 shadow-lg shadow-gray-100/50 p-6 md:p-8">
                        <div class="flex items-center justify-between mb-8 border-b border-gray-100 pb-5">
                            <div>
                                <h3 class="text-2xl font-black tracking-tight text-gray-900">Comments</h3>
                                <p class="text-sm text-gray-500 mt-1">Discussion history for this post</p>
                            </div>
                            <span class="bg-gray-100 text-gray-700 px-4 py-2 rounded-full text-xs font-black">
                                {{ $article->comments->count() }} Comments
                            </span>
                        </div>

                        <div class="space-y-6">
                            @foreach ($comments as $comment)
                            <div class="flex gap-4">
                                <a href="{{ route('admin.show.user',$comment->user) }}"  class="w-11 h-11 mt-1 rounded-full border border-gray-200 bg-[#0f0f0f] text-white shadow-sm overflow-hidden flex items-center justify-center font-bold text-xs uppercase select-none shrink-0">
                                    @if ($comment->user->avatar)
                                        <img src="{{ asset('storage/' . $comment->user->avatar) }}" alt="user_image" class="w-full h-full object-cover">
                                    @else
                                        <span>{{ Str::upper(Str::substr($comment->user->name, 0, 2)) }}</span>
                                    @endif
                                </a>

                                <div class="flex-1">
                                    <div class="bg-[#f0f0f0] rounded-2xl px-5 py-3">
                                        <div class="flex items-center justify-between mb-1">
                                            <h4 class="font-black text-gray-900 text-sm">
                                                {{ $comment->user->name }}
                                            </h4>
                                            <p class="text-xs font-extrabold text-gray-500">
                                                {{ $comment->created_at->diffForHumans() }}
                                            </p>
                                        </div>
                                        <p class="text-sm text-gray-700 leading-7">
                                            {{ $comment->body }}
                                        </p>
                                    </div>

                                    <div x-data="{ showReplies: false }">
                                        @if ($comment->replies->count() > 0)
                                            <div class="flex items-center gap-5 mt-2 ml-2 text-xs font-bold text-gray-500">
                                                <button @click="showReplies = !showReplies" class="hover:text-black transition flex items-center gap-1">
                                                    <span x-text="showReplies ? 'Hide' : 'View'"></span> {{ $comment->replies->count() }} nested reply
                                                </button>
                                            </div>
                                        @endif

                                        <div x-show="showReplies" class="mt-2 pl-4 border-l-2 border-gray-200 space-y-3" x-cloak>
                                            @foreach ($comment->replies as $reply)
                                            <div class="flex gap-4">
                                                <a href="{{ route('admin.show.user',$reply->user) }}"  class="w-9 h-9 mt-1 rounded-full object-cover border border-gray-200 bg-gray-800 text-white shadow-sm overflow-hidden flex items-center justify-center font-bold text-[10px] uppercase tracking-wider shrink-0">
                                                    @if ($reply->user->avatar)
                                                        <img src="{{ asset('storage/' . $reply->user->avatar) }}" alt="user_image" class="w-full h-full object-cover">
                                                    @else
                                                        <span>{{ Str::upper(Str::substr($reply->user->name, 0, 2)) }}</span>
                                                    @endif
                                                </a>

                                                <div class="flex-1">
                                                    <div class="bg-[#f0f0f0] rounded-2xl px-4 py-2.5">
                                                        <div class="flex items-center justify-between mb-1">
                                                            <h4 class="font-black text-gray-900 text-xs">
                                                                {{ $reply->user->name }}
                                                            </h4>
                                                            <p class="text-[10px] font-extrabold text-gray-500">
                                                                {{ $reply->created_at->diffForHumans() }}
                                                            </p>
                                                        </div>
                                                        <p class="text-sm text-gray-700 leading-6">
                                                            {{ $reply->body }}
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <aside class="lg:col-span-4 space-y-8">
                    <div class="bg-white rounded-[2rem] border border-gray-100 shadow-lg shadow-gray-100 p-8 sticky top-2">
                        <div class="border-l-4 border-black pl-4 mb-5">
                            <h3 class="text-2xl font-black tracking-tight text-gray-900">Article Insights</h3>
                            <p class="text-[10px] uppercase tracking-[0.2em] text-gray-400 font-black mt-1">Reader Statistics</p>
                        </div>

                        <div class="space-y-4">
                            <div class="flex items-center justify-between border-b border-gray-100 pb-4">
                                <span class="text-gray-500 font-semibold text-sm">Status</span>
                                <span class="bg-emerald-100 text-emerald-700 px-4 py-2 rounded-full text-xs font-black uppercase tracking-wide">
                                    {{ $article->status }}
                                </span>
                            </div>

                            <div class="flex items-center justify-between border-b border-gray-100 pb-4">
                                <span class="text-gray-500 font-semibold text-sm">Category</span>
                                <span class="font-black text-gray-900">
                                    {{ $article->category->name }}
                                </span>
                            </div>

                            <div class="flex items-center justify-between border-b border-gray-100 pb-4">
                                <span class="text-gray-500 font-semibold text-sm">Created</span>
                                <span class="font-black text-gray-900 text-right">
                                    {{ $article->created_at->format('M d, Y') }}
                                </span>
                            </div>

                            <div class="flex items-center justify-between border-b border-gray-100 pb-4">
                                <span class="text-gray-500 font-semibold text-sm">Likes</span>
                                <span class="font-black text-gray-900">
                                    {{ $likes->count() }}
                                </span>
                            </div>

                            <div class="flex items-center justify-between pb-2">
                                <span class="text-gray-500 font-semibold text-sm">Last Update</span>
                                <span class="font-black text-gray-900 text-right">
                                    {{ $article->updated_at->diffForHumans() }}
                                </span>
                            </div>

                            <a href="{{ route('admin.articles') }}" class="group inline-flex items-center gap-2 text-sm font-extrabold text-gray-500 hover:text-gray-800 transition-colors duration-150">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-4 h-4 text-gray-400 group-hover:text-gray-700 group-hover:-translate-x-1 transition-transform duration-150">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                                </svg>
                                <span>Back To Articles</span>
                            </a>
                        </div>
                    </div>
                </aside>

            </div>
        </div>
    </section>
</x-admin>
