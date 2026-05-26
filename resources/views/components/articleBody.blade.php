<section class="bg-[#f5f7fb] pb-20">
        <div class="relative overflow-hidden bg-black">
            <div class="absolute inset-0 opacity-20">
                <img src="{{ asset('storage/' . $article->cover_path) }}" alt="{{ $article->title }}" class="w-full h-full object-cover">
            </div>

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
                    @if (auth()->user()->role === 'author')
                    <div class="flex items-center gap-3">
                        <form action="/article/{{ $article->id }}/like" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="flex items-center gap-2 bg-white/5 hover:bg-white/10 border border-white/10 rounded-xl px-4 py-1.5 transition text-white text-sm font-bold group">
                                    @if($likes->count()>0)
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4 text-rose-500 scale-110 transition group-hover:scale-125">
                                            <path d="M11.645 20.91l-.007-.003-.022-.012a15.247 15.247 0 01-.383-.218 25.18 25.18 0 01-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0112 5.052 5.5 5.5 0 0116.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 01-4.244 3.17 15.247 15.247 0 01-.383.219l-.022.012-.007.004-.003.001a.752.752 0 01-.704 0l-.003-.001z" />
                                        </svg>
                                    @else
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 text-gray-400 group-hover:text-rose-400 transition group-hover:scale-110">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                                        </svg>
                                    @endif
                                <span>{{ $likes->count() }} Like</span>
                            </button>
                        </form>
                        <form action="/article/{{ $article->id }}/bookmark" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="flex items-center gap-2 bg-white/5 hover:bg-white/10 border border-white/10 rounded-xl px-4 py-1.5 transition text-white text-sm font-bold group">
                                @if($article->bookmarked())
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4 scale-110 transition group-hover:scale-125">
                                        <path fill-rule="evenodd" d="M6.32 2.577a49.255 49.255 0 0 1 11.36 0c1.497.174 2.57 1.46 2.57 2.93V21a.75.75 0 0 1-1.085.67L12 18.089l-7.165 3.583A.75.75 0 0 1 3.75 21V5.507c0-1.47 1.073-2.756 2.57-2.93Z" clip-rule="evenodd" />
                                    </svg>
                                    <span>Saved</span>
                                @else
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 text-gray-400 group-hover:text-amber-400 transition group-hover:scale-110">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.593 3.322c1.1.128 1.907 1.077 1.907 2.185V21L12 17.25 4.5 21V5.507c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0 1 11.186 0Z" />
                                    </svg>
                                    <span>Bookmark</span>
                                @endif
                            </button>
                        </form>
                    </div>
                    @endif
                </div>
                <h1 class="text-white text-3xl md:text-4xl lg:text-5xl font-black leading-tight tracking-tight max-w-5xl">
                    {{ $article->title }}
                </h1>
                <p class="mt-5 text-gray-300 text-base md:text-lg leading-relaxed max-w-3xl font-medium">
                    {{ $article->excerpt }}
                </p>
                <a href="/user/{{ $article->user->id }}" class="mt-7 flex flex-wrap items-center gap-5 text-white/80 text-sm font-semibold">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-white/10 border border-white/10 flex items-center justify-center text-sm font-black uppercase overflow-hidden">
                            @if ($article->user->avtar)
                                <img src="{{ asset('storage/' . $article->user->avtar) }}" alt="{{ $article->user->name }}" class="w-9 h-9">
                            @else
                                {{ substr($article->user->name, 0, 2) }}
                            @endif
                        </div>
                        <div>
                            <p class="font-black text-white uppercase tracking-wide text-xs">{{ $article->user->name }}</p>
                            <p class="text-gray-400 text-xs mt-0.5">Author</p>
                        </div>
                    </div>
                    <div class="h-8 w-px bg-white/10 hidden md:block"></div>
                    <div>
                        <p class="text-[10px] uppercase tracking-[0.2em] text-gray-500 font-black">Published</p>
                        <p class="mt-0.5 font-bold text-white text-sm">{{ $article->created_at->format('F d, Y') }}</p>
                    </div>
                    <div class="h-8 w-px bg-white/10 hidden md:block"></div>
                    <div>
                        <p class="text-[10px] uppercase tracking-[0.2em] text-gray-500 font-black">Views</p>
                        <p class="mt-0.5 font-bold text-white text-sm">{{ number_format($article->view_count) }} Views</p>
                    </div>
                </a>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-5 lg:px-8 mt-10">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
                <div class="lg:col-span-8">
                    <article class="bg-white rounded-[2rem] shadow-xl shadow-gray-200/40 border border-gray-100 overflow-hidden">
                        <div class="overflow-hidden">
                            @if ($article->cover_path)
                                <img src="{{ asset('storage/' . $article->cover_path) }}" alt="{{ $article->title }}" class="w-full h-[250px] md:h-[450px] object-cover hover:scale-105 duration-700">
                            @endif
                        </div>

                        <div class="p-6 md:p-10 lg:p-14">
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

                    {{-- AUTHOR CARD --}}
                    <a href="/user/{{ $article->user->id }}" class="block mt-10 bg-white rounded-[20px] border border-gray-100 shadow-lg shadow-gray-100/50 p-8 md:p-10 transition-all duration-300 ease-out hover:shadow-2xl hover:border-gray-200 hover:-translate-y-1 active:scale-[0.98] active:shadow-md active:translate-y-0 group">
                        <div class="flex flex-col md:flex-row md:items-center gap-6">
                            <div class="w-24 h-24 rounded-full bg-[#111827] text-white shrink-0 group-hover:scale-110 transition-transform duration-500 overflow-hidden flex items-center justify-center font-black text-xl uppercase tracking-wider select-none border border-gray-100">
                                @if ($article->user->avtar)
                                    <img src="{{ asset('storage/' . $article->user->avtar) }}" alt="{{ $article->user->name }}" class="w-full h-full object-cover">
                                @else
                                    <span>{{ Str::upper(Str::substr($article->user->name, 0, 2)) }}</span>
                                @endif
                            </div>
                            <div>
                                <p class="text-[11px] uppercase tracking-[0.2em] text-gray-400 font-black mb-2">Written By</p>
                                <h3 class="text-lg md:text-xl lg:text-2xl font-black text-gray-900 tracking-tight">{{ $article->user->name }}</h3>
                                <p class="mt-4 text-gray-600 leading-8 text-[15px] max-w-3xl">{{ $article->user->bio }}</p>
                            </div>
                        </div>
                    </a>

                    @if (auth()->user()->role === 'author')
                    <div class="mt-10 bg-white rounded-[2rem] border border-gray-100 shadow-lg shadow-gray-100/50 p-6 md:p-8">
                        <div class="flex items-center justify-between mb-8">
                            <div>
                                <h3 class="text-2xl font-black tracking-tight text-gray-900">Comments</h3>
                                <p class="text-sm text-gray-500 mt-1">Join the discussion</p>
                            </div>
                            <span class="bg-gray-100 text-gray-700 px-4 py-2 rounded-full text-xs font-black">{{ $article->comment->count() }} Comments</span>
                        </div>

                        <div class="flex gap-4 mb-8">
                            <div class="w-11 h-11 rounded-full bg-white/10 border border-white/10 flex items-center justify-center text-sm font-black uppercase overflow-hidden">
                                @if (auth()->user()?->avtar)
                                    <img src="{{ asset('storage/' . auth()->user()?->avtar) }}" alt="{{ auth()->user()?->name }}" class="w-11 h-11">
                                @else
                                    {{ substr(auth()->user()?->name, 0, 2) }}
                                @endif
                            </div>

                            <div class="flex-1">
                                <form action="/article/comment" method="post" enctype="multipart/form-data">
                                    @csrf
                                    <input type="hidden" name="article_id" value="{{ $article->id }}">
                                    <x-form.field name="body" type="textarea" label="Comment" placeholder="Start your story..."></x-form.field>
                                    @if ($errors->any())
                                        <div class="text-red-500">
                                            <ul>
                                                @foreach ($errors->all() as $error)
                                                    <li>{{ $error }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                    <div class="flex justify-end mt-3">
                                        <button class="bg-black hover:bg-gray-900 text-white px-6 py-3 rounded-xl text-sm font-black transition">
                                            Comment
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        {{-- COMMENTS LIST --}}
                        <div class="space-y-6">
                            @foreach ($comments as $comment)
                            <div class="flex gap-4">
                                <a href="/user/{{ $comment->user->id }}" class="w-11 h-11 mt-3 rounded-full border-2 border-[#0f0f0f] bg-[#0f0f0f] text-white shadow-sm overflow-hidden flex items-center justify-center font-bold text-xs uppercase select-none shrink-0">
                                    @if ($comment->user->avtar)
                                        <img src="{{ asset('storage/' . $comment->user->avtar) }}" alt="user_image" class="w-full h-full object-cover">
                                    @else
                                        <span>{{ Str::upper(Str::substr($comment->user->name, 0, 2)) }}</span>
                                    @endif
                                </a>

                                <div class="flex-1">
                                    <div class="bg-[#f0f0f0] rounded-2xl px-5 py-3">
                                        <div class="flex items-center justify-between mb-2">
                                            <div class="w-full flex justify-between">
                                                <h4 class="font-black text-gray-900 text-sm">
                                                    {{ $comment->user->name }}
                                                </h4>

                                                <p class="text-xs font-extrabold text-gray-600">
                                                    {{ $comment->created_at->diffForHumans() }}
                                                </p>
                                            </div>
                                        </div>
                                        <p class="text-sm text-gray-700 leading-7">
                                            {{ $comment->body }}
                                        </p>
                                    </div>

                                    <div x-data="{ activeReply: null, showReplies: false }">
                                        <div class="flex items-center gap-5 mt-3 ml-2 text-xs font-bold text-gray-500">
                                            <button @click="activeReply = activeReply === {{ $comment->id }} ? null : {{ $comment->id }}" class="hover:text-black transition">Reply</button>
                                            @if ($comment->replies->count() > 0)
                                                <button x-show="!showReplies" @click="showReplies = !showReplies" class="hover:text-black transition">View {{ $comment->replies->count() }} more reply</button>
                                            @endif
                                        </div>
                                        <div x-show="showReplies" class="py-4">
                                            @foreach ($replies as $reply)
                                            <div class="flex gap-4">
                                                <a href="/user/{{ $reply->user->id }}">
                                                    <div class="w-11 h-11 mt-3 rounded-full object-cover border-2 border-[#0f0f0f] shadow-sm overflow-hidden">
                                                        @if ($reply->user->avtar)
                                                            <img src="{{ asset('storage/' . $reply->user->avtar) }}" alt="user_image" class="w-full h-full object-cover">
                                                        @else
                                                            <span>{{ Str::upper(Str::substr($reply->user->name, 0, 2)) }}</span>
                                                        @endif
                                                    </div>
                                                </a>

                                                <div class="flex-1">
                                                    <div class="bg-[#f0f0f0] rounded-2xl px-5 py-2 my-2">
                                                        <div class="flex items-center justify-between mb-2">
                                                            <div class="w-full flex justify-between">
                                                                <h4 class="font-black text-gray-900 text-sm">
                                                                    {{ $reply->user->name }}
                                                                </h4>

                                                                <p class="text-xs font-extrabold text-gray-600">
                                                                    {{ $reply->created_at->diffForHumans() }}
                                                                </p>
                                                            </div>
                                                        </div>
                                                        <p class="text-sm text-gray-700 leading-7">
                                                            {{ $reply->body }}
                                                        </p>
                                                    </div>
                                                    <div x-show="showReplies" class="flex items-center gap-5 ml-2 text-xs font-bold text-gray-500">
                                                        <button class="hover:text-black transition">Like</button>
                                                        <button @click="activeReply = activeReply === {{ $reply->id }} ? null : {{ $reply->id }}" class="hover:text-black transition">Reply</button>
                                                    </div>
                                                    <div x-show="activeReply === {{ $reply->id }}">
                                                        <form action="/article/comment" method="post">
                                                            @csrf
                                                            <div class="w-full flex items-end gap-4">
                                                                <input type="hidden" name="article_id" value="{{ $article->id }}">
                                                                <input type="hidden" name="parent_id" value="{{ $reply->id }}">
                                                                <div class="flex-1 mt-2">
                                                                    <x-form.field name="body" type="text" label='' placeholder="Reply..."></x-form.field>
                                                                </div>

                                                                <button type="submit" class="bg-black hover:bg-gray-900 text-white px-6 h-10 rounded-xl text-sm font-black transition whitespace-nowrap">Comment</button>
                                                            </div>
                                                            @if ($errors->any())
                                                                <div class="text-red-500 mt-2 text-sm">
                                                                    @foreach ($errors->all() as $error)
                                                                        <p>{{ $error }}</p>
                                                                    @endforeach
                                                                </div>
                                                            @endif
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>
                                        <div x-show="activeReply === {{ $comment->id }}">
                                            <form action="/article/comment" method="post">
                                                @csrf
                                                <div class="w-full flex items-end gap-4">
                                                    <input type="hidden" name="article_id" value="{{ $article->id }}">
                                                    <input type="hidden" name="parent_id" value="{{ $comment->id }}">
                                                    <div class="flex-1 mt-2">
                                                        <x-form.field name="body" type="text" label='' placeholder="Reply..."></x-form.field>
                                                    </div>

                                                    <button type="submit" class="bg-black hover:bg-gray-900 text-white px-6 h-10 rounded-xl text-sm font-black transition whitespace-nowrap">Comment</button>
                                                </div>
                                                @if ($errors->any())
                                                    <div class="text-red-500 mt-2 text-sm">
                                                        @foreach ($errors->all() as $error)
                                                            <p>{{ $error }}</p>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>

                <aside class="lg:col-span-4 space-y-8">
                    <div class="bg-white rounded-[2rem] border border-gray-100 shadow-lg shadow-gray-100 p-8 sticky top-20">
                        <div class="border-l-4 border-black pl-4 mb-5">
                            <h3 class="text-2xl font-black tracking-tight text-gray-900">
                                Article Insights
                            </h3>
                            <p class="text-[10px] uppercase tracking-[0.2em] text-gray-400 font-black mt-1">
                                Reader Statistics
                            </p>
                        </div>

                        <div class="space-y-5">
                            <div class="flex items-center justify-between border-b border-gray-100 pb-4">
                                <span class="text-gray-500 font-semibold text-sm">Status</span>
                                <span class="bg-emerald-100 text-emerald-700 px-4 py-2 rounded-full text-xs font-black uppercase tracking-wide">
                                    {{ $article->status }}
                                </span>
                            </div>

                            <div class="flex items-center justify-between border-b border-gray-100 pb-4">
                                <span class="text-gray-500 font-semibold text-sm">Views</span>
                                <span class="font-black text-gray-900">
                                    {{ number_format($article->view_count) }}
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

                            <div class="flex items-center justify-between">
                                <span class="text-gray-500 font-semibold text-sm">Last Update</span>
                                <span class="font-black text-gray-900 text-right">
                                    {{ $article->updated_at->diffForHumans() }}
                                </span>
                            </div>
                            @if (auth()->user()->role === 'author')
                                <a href="/articles" class="group inline-flex items-center gap-2 text-sm font-extrabold text-gray-500 hover:text-gray-800 transition-colors duration-150">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-4 h-4 text-gray-400 group-hover:text-gray-700 group-hover:-translate-x-1 transition-transform duration-150">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                                    </svg>
                                    <span>Back To Articles</span>
                                </a>
                            @else
                                <a href="/admin/articles" class="group inline-flex items-center gap-2 text-sm font-extrabold text-gray-500 hover:text-gray-800 transition-colors duration-150">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-4 h-4 text-gray-400 group-hover:text-gray-700 group-hover:-translate-x-1 transition-transform duration-150">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                                    </svg>
                                    <span>Back To Articles</span>
                                </a>
                            @endif
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </section>
