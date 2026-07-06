<?php

use Livewire\Attributes\Layout;
use Livewire\Component;
use App\Models\User;
use App\Models\Article;
use Livewire\Attributes\On;
use App\Events\ArticleCreate;

new #[Layout('layouts::dashboard')] class extends Component
{
    public User $user;
    public $articles;

    #[On('echo:articles,ArticleCreate')]
    public function loadArticles()
    {
        $this->articles = $this->user->articles()->with(['category'])->latest()->get();
    }
    public function mount() {
        // $this->articles = $this->user->articles()->with(['category'])->latest()->get();
        $this->loadArticles();
    }
};
?>

<div>
    <section class="max-w-7xl mx-auto">
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-6 m-2">
            <div>
                <h1 class="mt-2 text-5xl font-black tracking-tight text-gray-900">Latest Articles</h1>
                <p class="mt-4 text-sm text-gray-500 font-medium max-w-lg leading-relaxed">Explore fresh perspectives and deep dives from our community of thinkers.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 mt-10">
            @if($articles->count() > 0)
                @foreach ($this->articles as $article)
                    <a href='{{ route('admin.article.show',$article) }}' wire:navigate class="p-5 bg-[#efefef] rounded-2xl">
                        <h3 class="text-xl line-clamp-1 font-black leading-tight tracking-tight text-gray-800">{{ $article->title }}</h3>
                        <p class="mt-2 h-10 text-gray-600 text-sm leading-relaxed line-clamp-2">{{ $article->excerpt }}</p>
                        <div class="mt-5 flex items-center justify-between border-t border-gray-100">
                            <div>
                                <p class="text-[10px] uppercase tracking-[0.18em] font-bold text-gray-400">Published</p>
                                <p class="text-xs font-semibold text-gray-700 mt-0.5">{{ $article->publish_at->diffForHumans() }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-[10px] uppercase tracking-[0.18em] font-bold text-gray-400">Date</p>
                                <p class="text-xs font-semibold text-gray-700 mt-0.5">{{ $article->created_at->format('d M Y') }}</p>
                            </div>
                        </div>
                    </a>
                @endforeach
            @else
                <div class="bg-white border border-gray-200 rounded-[3rem] p-20 text-center shadow-sm">
                    <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                    <h2 class="text-2xl font-black text-gray-900">No articles published yet</h2>
                    <p class="text-gray-500 mt-2 font-medium">This user hasn't published any content yet.</p>
                </div>
            @endif
        </div>
    </section>
</div>
