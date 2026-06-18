<?php

use Livewire\Component;
use App\Models\Article;
use Illuminate\Support\Facades\Auth;

new class extends Component
{
    public $articles;

    public function mount() {
        $this->articles = Auth::user()->bookmarkedArticles()->with(['user', 'category'])->latest()->get();
    }
};
?>

<div class="py-12 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

    <div class="flex items-center space-x-3 mb-10 border-b border-gray-200 pb-5">
        <div class="p-2.5 bg-gray-100 rounded-xl text-gray-900">
            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                <path d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
            </svg>
        </div>
        <div>
            <h1 class="text-3xl font-bold tracking-tight text-gray-900">Bookmarked Articles</h1>
            <p class="text-sm text-gray-500 mt-1">Your curated list of saved reading material and insights.</p>
        </div>
    </div>

    @if($articles->isEmpty())
        <div class="text-center py-20 bg-white rounded-2xl border border-dashed border-gray-300 shadow-sm max-w-md mx-auto">
            <div class="inline-flex p-4 bg-gray-50 rounded-full text-gray-400 mb-4">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.593 3.322c1.1.128 1.907 1.077 1.907 2.185V21L12 17.25 4.5 21V5.507c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0111.186 0z"/>
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900">No bookmarks yet</h3>
            <p class="text-sm text-gray-500 mt-1 max-w-xs mx-auto px-4">Articles you save will show up here for easy access later.</p>
            <a href="{{ route('articles.index') }}" class="mt-5 inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-[#1a1a1a] rounded-full hover:bg-gray-800 transition">
                Browse Articles
            </a>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @auth
                @include('components.articleLayout')
            @endauth
        </div>
    @endif
</div>
