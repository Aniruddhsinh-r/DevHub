<?php

use Livewire\Component;
use App\Models\Article;
use App\Models\View;
use App\Models\Like;
use App\Enums\UserRole;
use App\Enums\ArticleStatus;
use App\Events\ArticleCreate;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;

new class extends Component
{
    public Article $article;
    public $viewed;
    public $comments;

    public function mount() {
        if ($this->article->status !== ArticleStatus::PUBLISHED && $this->article->user_id !== auth()->id()) {
            session()->flash('error', 'The article you are looking for is not available.');
            return $this->redirect('/app/articles', navigate: true);
        }

        $this->viewed = Auth::check() && Auth::user()->views()->where('article_id', $this->article->id)->exists();
        $this->comments = $this->article->comments()->whereNull('parent_id')->with(['user', 'replies.user'])->get();

        if (Auth::check() && Auth::id() !== $this->article->user_id && !$this->viewed) {
            Article::where('id', $this->article->id)->increment('view_count');
            View::create(['user_id' => Auth::id(), 'article_id' => $this->article->id]);
        }
        $this->article->refresh();
        ArticleCreate::dispatch();
        return;
    }

    public function toggleLike() {
        Gate::authorize('like', $this->article);

        $like = $this->article->likes()->where('user_id', auth()->id())->first();
        if ($like) {
            $like->delete();
            ArticleCreate::dispatch();
            $this->dispatch('live-notification', message: 'article unlike');
            return;
        }

        Like::create(['user_id' => auth()->id(),'article_id' => $this->article->id,]);
        ArticleCreate::dispatch();
        $this->dispatch('live-notification', message: 'article like');
    }

    public function toggleBookmark()
    {
        Gate::authorize('bookmark', $this->article);
        Auth::user()->bookmarkedArticles()->toggle($this->article->id);

        $message = $this->article->isBookmarkedByMe() ? 'article bookmark' : 'remove from bookmark';
        ArticleCreate::dispatch();
        $this->dispatch('live-notification', message: $message);
    }
};
?>

<div>
    <section class="bg-[#f5f7fb] pb-20">
        <div class="relative overflow-hidden bg-black">
            <div class="absolute inset-0 opacity-20">
                @unless(empty($article->cover_path))
                    <img src="{{ asset('storage/' . $article->cover_path) }}" alt="{{ $article->title }}" class="w-full h-full object-cover">
                @endunless
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
                    @if ($article->status === ArticleStatus::PUBLISHED)
                    <div class="flex items-center gap-3">
                        <div>
                            <button wire:click="toggleLike" type="submit" data-test="like-button"
                                class="flex items-center gap-2 bg-white/5 hover:bg-white/10 border border-white/10 rounded-xl px-4 py-1.5 transition text-white text-sm font-bold group">
                                @if ($article->isLikedByUser())
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                                        class="w-4 h-4 text-rose-500 scale-110 transition group-hover:scale-125">
                                        <path
                                            d="M11.645 20.91l-.007-.003-.022-.012a15.247 15.247 0 01-.383-.218 25.18 25.18 0 01-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0112 5.052 5.5 5.5 0 0116.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 01-4.244 3.17 15.247 15.247 0 01-.383.219l-.022.012-.007.004-.003.001a.752.752 0 01-.704 0l-.003-.001z" />
                                    </svg>
                                @else
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5"
                                        stroke="currentColor"
                                        class="w-4 h-4 text-gray-400 group-hover:text-rose-400 transition group-hover:scale-110">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                                    </svg>
                                @endif
                                <span>{{ $article->likes->count() }} Like</span>
                            </button>
                        </div>
                        <div>
                            <button wire:click="toggleBookmark" data-test="bookmark-button" class="flex items-center gap-2 bg-white/5 hover:bg-white/10 border border-white/10 rounded-xl px-4 py-1.5 transition text-white text-sm font-bold group">
                                @if($article->isBookmarkedByMe())
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
                        </div>
                    </div>
                    @endif
                </div>
                <h1 class="text-white text-3xl md:text-4xl lg:text-5xl font-black leading-tight tracking-tight max-w-5xl">
                    {{ $article->title }}
                </h1>
                <p class="mt-5 text-gray-300 text-base md:text-lg leading-relaxed max-w-3xl font-medium">
                    {{ $article->excerpt }}
                </p>
                <div class="mt-7 flex flex-wrap items-center gap-5 text-white/80 text-sm font-semibold">
                    @if ($article->user->hasRole(UserRole::AUTHOR))
                        <a href="{{ route('profile.show',$article->user) }}" wire:navigate class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-white/10 border border-white/10 flex items-center justify-center text-sm font-black uppercase overflow-hidden">
                                @if ($article->user->avatar)
                                    <img src="{{ asset('storage/' . $article->user->avatar) }}" alt="{{ $article->user->name }}" class="w-9 h-9">
                                @else
                                    {{ substr($article->user->name, 0, 2) }}
                                @endif
                            </div>
                            <div>
                                <p class="font-black text-white uppercase tracking-wide text-xs">{{ $article->user->name }}</p>
                                <p class="text-gray-400 text-xs mt-0.5">Author</p>
                            </div>
                        </a>
                    @endif
                    <div class="h-8 w-px bg-white/10 hidden md:block"></div>
                    <div>
                        <p class="text-[10px] uppercase tracking-[0.2em] text-gray-500 font-black">{{$article->status}}</p>
                        <p class="mt-0.5 font-bold text-white text-sm">{{ $article->published_at?->diffForHumans() ?? 'Not published'}}</p>
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

                            <div class="prose prose-lg max-w-none break-words prose-headings:font-black prose-headings:text-gray-900 prose-p:text-gray-700 prose-p:leading-9 prose-p:text-[17px] prose-img:rounded-3xl prose-a:text-black hover:prose-a:text-gray-600">
                                {!! nl2br(e($article->body)) !!}
                            </div>
                        </div>
                    </article>

                    {{-- AUTHOR CARD --}}
                    @if ($article->user->hasRole(UserRole::AUTHOR))
                        <a href="{{ route('profile.show',$article->user) }}" wire:navigate class="block mt-10 bg-white rounded-[20px] border border-gray-100 shadow-lg shadow-gray-100/50 p-8 md:p-10 transition-all duration-300 ease-out hover:shadow-2xl hover:border-gray-200 hover:-translate-y-1 active:scale-[0.98] active:shadow-md active:translate-y-0 group">
                            <div class="flex flex-col md:flex-row md:items-center gap-6">
                                <div class="w-24 h-24 rounded-full bg-[#111827] text-white shrink-0 group-hover:scale-110 transition-transform duration-500 overflow-hidden flex items-center justify-center font-black text-xl uppercase tracking-wider select-none border border-gray-100">
                                    @if ($article->user->avatar)
                                        <img src="{{ asset('storage/' . $article->user->avatar) }}" alt="{{ $article->user->name }}" class="w-full h-full object-cover">
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
                    @endif

                    @role('author')
                        @if ($article->status === ArticleStatus::PUBLISHED)
                            <livewire:livewirecomponent.post-comment :article="$article" />
                        @endif
                    @endrole
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
                            @role('author')
                                <a href="/app/articles" wire:navigate class="group inline-flex items-center gap-2 text-sm font-extrabold text-gray-500 hover:text-gray-800 transition-colors duration-150">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-4 h-4 text-gray-400 group-hover:text-gray-700 group-hover:-translate-x-1 transition-transform duration-150">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                                    </svg>
                                    <span>Back To Articles</span>
                                </a>
                            @else
                                <a href="/app/articles" wire:navigate class="group inline-flex items-center gap-2 text-sm font-extrabold text-gray-500 hover:text-gray-800 transition-colors duration-150">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-4 h-4 text-gray-400 group-hover:text-gray-700 group-hover:-translate-x-1 transition-transform duration-150">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                                    </svg>
                                    <span>Back To Articles</span>
                                </a>
                            @endrole
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </section>
</div>
