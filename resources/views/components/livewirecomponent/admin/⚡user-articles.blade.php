<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use Livewire\Component;
use App\Models\Article;
use App\Models\User;

new #[Layout('layouts::dashboard')] class extends Component
{
    #[Computed]
    public User $user;

    public function mount() {
        $this->articles = $this->user->articles()->with(['category'])->latest()->get();
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
            @foreach ($this->articles as $article)
                <a href='{{ route('admin.article.show',$article) }}' wire:navigate class="p-5 bg-[#efefef] rounded-2xl">
                    <h3 class="text-xl line-clamp-1 font-black leading-tight tracking-tight text-gray-800">{{ $article->title }}</h3>
                    <p class="mt-2 h-10 text-gray-600 text-sm leading-relaxed line-clamp-2">{{ $article->excerpt }}</p>
                    <div class="mt-5 flex items-center justify-between border-t border-gray-100">
                        <div>
                            <p class="text-[10px] uppercase tracking-[0.18em] font-bold text-gray-400">Published</p>
                            <p class="text-xs font-semibold text-gray-700 mt-0.5">{{ $article->created_at->diffForHumans() }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-[10px] uppercase tracking-[0.18em] font-bold text-gray-400">Date</p>
                            <p class="text-xs font-semibold text-gray-700 mt-0.5">{{ $article->created_at->format('d M Y') }}</p>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    </section>
</div>
