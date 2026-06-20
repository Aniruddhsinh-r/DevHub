<?php

use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

new class extends Component
{
    public $articles;
    public function mount() {
        $user = Auth::user();
        $this->articles = $user->articles()->where('status', 'draft')->latest()->get();
    }
};
?>

<section class="max-w-7xl mx-auto px-4 md:px-8 py-10">
    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-6 mb-10">
        <div>
            <div class="inline-block px-3 py-1 mb-4 text-[9px] font-black tracking-[0.2em] uppercase bg-[#d9d9d7] text-gray-600 rounded-md border border-gray-300">Private Workspace</div>
            <h2 class="text-3xl md:text-5xl font-black tracking-tighter text-[#181818] leading-none">Draft Articles</h2>
            <p class="mt-4 text-sm text-gray-500 font-medium leading-relaxed">Refine unfinished stories and continue writing before publishing them publicly.</p>
        </div>

        <a href="{{ route('publishedarticle') }}" class="bg-[#111111] text-white px-5 py-3 rounded-2xl text-[11px] font-black uppercase tracking-[0.18em] hover:bg-black transition-all duration-300">All Articles</a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        @foreach ($articles as $article)
            <a class="group bg-[#cfcfcf] border border-[#d7d7d6] rounded-[1.7rem] p-5 hover:-translate-y-1 hover:border-[#111111] transition-all duration-500 shadow-lg" href="{{ route('articles.edit',$article) }}">
                <div class="flex items-center justify-between mb-5">
                    <span class="px-3 py-1.5 rounded-full bg-[#111111] text-white text-[9px] font-black uppercase tracking-[0.18em]">Draft</span>
                    <div class="inline-block px-3 py-1 rounded-full bg-[#e7e7e4] text-[9px] font-black uppercase tracking-[0.15em] text-gray-600 border border-gray-300 mb-4">Technology</div>
                </div>

                <div class="mb-5">
                    <h3 class="text-xl font-black tracking-tight text-[#111111] leading-snug">{{ $article->title }}</h3>
                    <p class="mt-3 text-[13px] text-gray-600 leading-6 font-medium line-clamp-3">{{ $article->excerpt }}</p>
                </div>

                <div class="pt-4 border-t border-dashed border-gray-300 flex items-center justify-between">
                    <div>
                        <p class="text-[9px] uppercase tracking-[0.18em] text-gray-400 font-black">Updated</p>
                        <p class="mt-1 text-xs font-bold text-[#111111]">{{ $article->created_at->diffForHumans()}}</p>
                    </div>

                    <button class="px-4 py-2.5 rounded-xl bg-[#111111] text-white text-[10px] font-black uppercase tracking-[0.15em] hover:bg-black transition-all duration-300">Edit</button>
                </div>
            </a>
        @endforeach
    </div>
</section>
