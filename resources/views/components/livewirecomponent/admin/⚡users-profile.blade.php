<?php

use Livewire\Attributes\Layout;
use Livewire\Component;
use App\Models\User;

new #[Layout('layouts::dashboard')] class extends Component
{
    public $articles;
    public User $user;

    public function mount() {
        $this->articles = $this->user->articles()->with(['category'])->latest()->get();
    }
};
?>

<div>
    <div class="max-w-6xl mx-auto space-y-6">
            <div class="bg-[#fcfcfb] border border-gray-200 rounded-[1.5rem] shadow-sm p-6">
                <div class="grid grid-cols-1 lg:grid-cols-[220px_1fr_300px] gap-8 items-center">
                    <div class="flex justify-center lg:justify-start">
                    @if ($user->avatar)
                        <img src="{{ asset('storage/' . $user->avatar) }}" alt="{{ $user->name }}" class="w-56 h-56 rounded-full object-cover border-4 border-[#f3f3f1] shadow-sm">
                    @else
                        <div class="w-56 h-56 rounded-full bg-[#ececea] flex items-center justify-center text-6xl font-black text-[#111111] border-4 border-[#f3f3f1]">
                            {{ substr($user->name, 0, 2) }}
                        </div>
                    @endif
                    </div>

                    <div class="space-y-6">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">{{ $user->name }}</h1>
                            <p class="text-sm text-gray-600 mt-1 capitalize">{{ $user->getRoleNames()->first() }} Fullstack Web Developer</p>
                        </div>

                        <p class="text-gray-600 leading-6 text-sm max-w-xl">{{ $user->bio }}</p>
                        <div class="flex items-center gap-10 pt-1">
                            <div class="flex gap-2">
                                <h2 class="text-xl font-bold text-gray-900">{{ number_format($user->following()->count()) }}</h2>
                                <p class="text-gray-700 text-sm mt-1">Following</p>
                            </div>
                            <div class="w-px h-10 bg-gray-300"></div>
                            <div class="flex gap-2">
                                <h2 class="text-xl font-bold text-gray-900">{{ number_format($user->followers()->count()) }}</h2>
                                <p class="text-gray-700 text-sm mt-1">Followers</p>
                            </div>
                        </div>
                    </div>

                    <div class="border-l border-gray-200 pl-5 flex flex-col justify-between h-full">
                        <div class="space-y-6">
                            <div class="flex items-center justify-between gap-4">
                                <span class="text-gray-500 font-medium">Role</span>
                                <span class="text-gray-900 font-semibold capitalize">{{ $user->getRoleNames()->first() }}</span>
                            </div>

                            <div class="flex items-center justify-between gap-4">
                                <span class="text-gray-500 font-medium">Last Seen</span>
                                <span class="text-gray-900 font-semibold">{{ $user->last_seen_at ? $user->last_seen_at->diffForHumans() : 'Recently Active' }}</span>
                            </div>

                            <div class="flex items-center justify-between gap-4">
                                <span class="text-gray-500 font-medium">Joined At</span>
                                <span class="text-gray-900 font-semibold">{{ $user->created_at->format('M d, Y') }}</span>
                            </div>

                            <div class="flex items-center justify-between gap-4">
                                <span class="text-gray-500 font-medium">Total Articles</span>
                                <span class="text-gray-900 font-semibold">{{ $articles->count() }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-[#fcfcfb] border border-gray-200 rounded-[2rem] shadow-sm p-8">
                <div class="flex items-center justify-between mb-5">
                    <h2 class="text-xl font-bold text-gray-900">
                        {{ auth()->id() === $user->id ? 'My Articles' : $user->name . "'s Articles" }}
                    </h2>
                </div>

                @if($articles->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
                        @foreach ($articles->take(3) as $article)
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
                @else
                    <div class="bg-white border border-gray-200 rounded-[3rem] p-10 mx-10 text-center shadow-sm">
                        <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-6">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                        </div>
                        <h2 class="text-2xl font-black text-gray-900">No articles published yet</h2>
                        <p class="text-gray-500 mt-2 font-medium">This user hasn't published any content yet.</p>
                    </div>
                @endif

            @if ($articles->count() > 3)
                <a href="{{ route('admin.user.published',$user) }}" wire:navigate class="flex justify-center mt-8">
                    <button class="border border-gray-300 hover:border-black hover:bg-black hover:text-white transition px-6 py-2.5 rounded-xl text-sm font-semibold text-gray-800">Show More Articles</button>
                </a>
            @endif
            </div>
    </div>
</div>
