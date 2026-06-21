<?php

use Livewire\Component;
use App\Models\Article;
use App\Models\Comment;
use App\Models\Like;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use App\Models\User;
use App\Models\View;

new #[Layout('layouts::dashboard')] class extends Component
{
    #[Computed]
    public $articleCount = null;
    public $users = null;
    public $comments = null;
    public $views = null;
    public $likes = null;
    public $topUser;

    public function mount() {
        $this->articleCount = Article::where('status', 'published')->count();
        $this->articles = Article::where('status', 'published')->latest()->take(4)->get();
        $this->users = User::count();
        $this->comments = Comment::count();
        $this->views = View::count();
        $this->likes = Like::count();
        $this->topUser = User::withCount('articles')->orderByDesc('articles_count')->first();
    }
};
?>

    <div class="space-y-4 max-w-[1600px] mx-auto">
        {{-- TOP HEADER --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-2xl font-black tracking-tight text-[#111827]">
                    Dashboard Overview
                </h1>
                <p class="text-xs text-gray-500 mt-0.5">
                    Welcome back, {{ auth()->user()->name }}
                </p>
            </div>

            <div class="bg-white border border-gray-200 rounded-xl px-4 py-2 shadow-sm self-start sm:self-auto">
                <p class="text-[10px] uppercase tracking-[0.2em] text-gray-400 font-black">
                    Today
                </p>
                <p class="text-xs font-bold text-gray-700 mt-0.5">
                    {{ now()->format('d M Y') }}
                </p>
            </div>
        </div>

        {{-- STATS GRID --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
            {{-- ARTICLES --}}
            <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <div class="w-9 h-9 rounded-lg bg-gray-100 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor" class="w-5 h-5 text-gray-700">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                        </svg>
                    </div>
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-[0.15em]">
                        Articles
                    </span>
                </div>
                <h2 class="mt-4 text-2xl font-black text-[#111827]">
                    {{ $articleCount }}
                </h2>
                <p class="mt-1 text-xs text-gray-400">
                    Total published articles
                </p>
            </div>

            {{-- USERS --}}
            <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <div class="w-9 h-9 rounded-lg bg-gray-100 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor" class="w-5 h-5 text-gray-700">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Z" />
                        </svg>
                    </div>
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-[0.15em]">
                        Users
                    </span>
                </div>
                <h2 class="mt-4 text-2xl font-black text-[#111827]">
                    {{ $users }}
                </h2>
                <p class="mt-1 text-xs text-gray-400">
                    Registered users
                </p>
            </div>

            {{-- COMMENTS --}}
            <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <div class="w-9 h-9 rounded-lg bg-gray-100 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor" class="w-5 h-5 text-gray-700">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 9.75a.375.375 0 11-.75 0 .375.375 0 01.75 0Zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0Zm0 0H12m3.75 0a.375.375 0 11-.75 0 .375.375 0 01.75 0Zm0 0H15m-8.25 8.25h10.5A2.25 2.25 0 0019.5 15.75v-7.5A2.25 2.25 0 0017.25 6H6.75A2.25 2.25 0 004.5 8.25v7.5A2.25 2.25 0 006.75 18Z" />
                        </svg>
                    </div>
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-[0.15em]">
                        Comments
                    </span>
                </div>
                <h2 class="mt-4 text-2xl font-black text-[#111827]">
                    {{ $comments }}
                </h2>
                <p class="mt-1 text-xs text-gray-400">
                    Total article comments
                </p>
            </div>

            {{-- VIEWS --}}
            <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <div class="w-9 h-9 rounded-lg bg-gray-100 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor" class="w-5 h-5 text-gray-700">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12S5.25 5.25 12 5.25 21.75 12 21.75 12 18.75 18.75 12 18.75 2.25 12 2.25 12Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15.75a3.75 3.75 0 100-7.5 3.75 3.75 0 000 7.5Z" />
                        </svg>
                    </div>
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-[0.15em]">
                        Views
                    </span>
                </div>
                <h2 class="mt-4 text-2xl font-black text-[#111827]">
                    {{ $views }}
                </h2>
                <p class="mt-1 text-xs text-gray-400">
                    Total article views
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-4 items-start">
            <div class="xl:col-span-2 bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
                <div class="mb-4">
                    <h2 class="text-lg font-black text-[#111827]">
                        Latest Article Analysis
                    </h2>
                    <p class="text-xs text-gray-500 mt-0.5">
                        Performance breakdown of your latest published articles.
                    </p>
                </div>

                <div class="space-y-2">
                    @foreach ($this->articles as $article)
                        <a href="{{ route('admin.article.show',$article) }}" class="flex items-center justify-between p-3 bg-[#f3f4f6] rounded-xl hover:bg-gray-200/60 transition group">
                            <div class="flex items-center gap-3 truncate max-w-[70%]">
                                <div class="w-6 h-6 rounded-md bg-gray-900 text-white flex items-center justify-center font-bold text-[10px] shrink-0">{{ $loop->iteration }}</div>
                                <span class="text-xs font-bold text-gray-900 truncate">{{ $article->title }}</span>
                            </div>
                            <div class="flex items-center gap-4 text-right">
                                <div>
                                    <span class="text-[9px] uppercase font-black text-gray-400 tracking-wider block">Views</span>
                                    <span class="text-xs font-black text-[#111827]">{{ $article->view_count }} Views</span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>

            <div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-[0_8px_30px_rgb(0,0,0,0.02)] flex flex-col gap-6 max-w-sm">
                <div class="flex items-center justify-between border-b border-gray-50 pb-4">
                    <div>
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-1">Remaining Drafts</span>
                        <span class="text-3xl font-black text-gray-900 tracking-tight">{{ $this->articles->where('status','draft')->count() }}</span>
                    </div>
                    <span class="p-2 bg-amber-50 text-amber-600 rounded-xl text-xs font-semibold">Drafts</span>
                </div>

                <div class="flex items-center justify-between border-b border-gray-50 pb-4">
                    <div>
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-1">Total Likes</span>
                        <span class="text-3xl font-black text-gray-900 tracking-tight">{{ $likes }}</span>
                    </div>
                    <span class="p-2 bg-rose-50 text-rose-600 rounded-xl text-xs font-semibold">❤️ Likes</span>
                </div>

                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block">Top Contributor</span>
                        <span class="text-[9px] font-extrabold bg-emerald-50 text-emerald-700 uppercase tracking-wider px-2 py-0.5 rounded-full border border-emerald-100 animate-pulse">
                            {{ $topUser->articles_count }} Posts
                        </span>
                    </div>

                    <a href="{{ route('admin.show.user',$topUser) }}" class="relative group flex items-start gap-3.5 p-3.5 rounded-2xl bg-gradient-to-br from-gray-50 via-white to-gray-50/50 border border-gray-100 shadow-sm transition-all duration-300 hover:shadow-md hover:border-gray-200">
                        <div class="relative shrink-0">
                            @if ($topUser->avatar)
                                <img src="{{ asset('storage/' . $topUser->avatar) }}" alt="{{ $topUser->name }}" class="w-10 h-10 rounded-full bg-black shrink-0 group-hover:scale-110 transition-transform duration-500 object-cover">
                            @else
                                <div class="w-10 h-10 rounded-full bg-black text-white flex items-center justify-center text-sm font-black uppercase shrink-0 group-hover:scale-110 transition-transform duration-500">
                                    {{ mb_substr($topUser->name, 0, 2) }}
                                </div>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex flex-col">
                                <span class="text-sm font-bold text-gray-900 tracking-tight hover:text-indigo-600 transition-colors cursor-pointer truncate">
                                    {{ $topUser->name }}
                                </span>
                                <span class="text-[11px] text-gray-400 font-medium tracking-normal truncate mt-0.5">
                                    {{ $topUser->email }}
                                </span>
                            </div>

                            <p class="text-[11px] text-gray-500/90 font-normal mt-2 line-clamp-2 leading-relaxed whitespace-normal pr-1">
                                {{ $topUser->bio }}
                            </p>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
