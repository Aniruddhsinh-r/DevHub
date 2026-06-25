<?php

use App\Models\Article;
use App\Models\Comment;
use App\Notifications\CommentNotification;
use Livewire\Attributes\Computed;
use App\Enums\ArticleStatus;
use Livewire\Attributes\Validate;
use Livewire\Component;
use App\Enums\UserRole;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    public Article $article;
    public string $body = '';
    public string $replybody = '';

    #[Computed]
    public function comments()
    {
        return $this->article->comments()->whereNull('parent_id')->with(['user', 'replies.user'])->latest()->get();
    }

    public function postComment() {
        $validated = $this->validate([
            'body' => 'required|string|max:5000'
        ]);

        if (!auth()->user()?->hasRole(UserRole::AUTHOR)) {
            session()->flash('error', 'Only Author can post comment on article.');
            return $this->redirectRoute('/', navigate: true);
        }

        if ($this->article->status !== ArticleStatus::PUBLISHED) {
            $this->dispatch('live-notification', message: 'Comments are only allowed on published articles.');
            return;
        }

        $comment = Comment::create([
            'user_id' => Auth::id(),
            'article_id' => $this->article->id,
            'parent_id' => null,
            'body' => $this->body,
        ]);

        if ($this->article->user_id !== Auth::id()) {
            $this->article->user->notify(new CommentNotification($comment));
        }

        $this->body = '';
        $this->dispatch('live-notification', message: 'Comment successfully posted.');
    }

    public function postReply($parentId) {
        $validated = $this->validate([
            'replybody' => 'required|string|max:5000'
        ]);

        if (!auth()->user()?->hasRole(UserRole::AUTHOR)) {
            session()->flash('error', 'Only Author can reply on comments.');
            return $this->redirectRoute('/', navigate: true);
        }

        $comment = Comment::create([
            'user_id' => Auth::id(),
            'article_id' => $this->article->id,
            'parent_id' => $parentId,
            'body' => $this->replybody,
        ]);

        $parent = Comment::with('user')->find($parentId);
        if ($parent && $parent->user_id !== Auth::id()) {
            $parent->user->notify(new CommentNotification($comment));
        }

        $this->replybody = '';

        $this->dispatch('live-notification', message: 'Reply successfully posted.');
    }
};
?>

<div class="mt-10 bg-white rounded-[2rem] border border-gray-100 shadow-lg shadow-gray-100/50 p-6 md:p-8">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h3 class="text-2xl font-black tracking-tight text-gray-900">Comments</h3>
            <p class="text-sm text-gray-500 mt-1">Join the discussion</p>
        </div>
        <span class="bg-gray-100 text-gray-700 px-4 py-2 rounded-full text-xs font-black">
            {{ $article->comments->count() }} Comments
        </span>
    </div>

    <div class="flex gap-4 mb-8">
        <div class="w-11 h-11 rounded-full bg-white/10 border border-white/10 flex items-center justify-center text-sm font-black uppercase overflow-hidden">
            @if (auth()->user()?->avatar)
                <img src="{{ asset('storage/' . auth()->user()?->avatar) }}" alt="{{ auth()->user()?->name }}" class="w-11 h-11">
            @else
                {{ substr(auth()->user()?->name, 0, 2) }}
            @endif
        </div>

        <div class="flex-1">
            <form wire:submit.prevent="postComment">
                <div class="w-full">
                    <textarea wire:model="body" name="body" placeholder="Start your story..." class="w-full border border-gray-200 rounded-2xl p-4 text-sm focus:outline-none focus:ring-2 focus:ring-black"></textarea>
                </div>
                @error('body') <div class="text-red-500 text-xs mt-1">{{ $message }}</div> @enderror

                <div class="flex justify-end mt-3">
                    <button type="submit" class="bg-black hover:bg-gray-900 text-white px-6 py-3 rounded-xl text-sm font-black transition">
                        <span wire:loading wire:target="postComment">commenting...</span>
                        <span wire:loading.remove wire:target="postComment">Comment</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- COMMENTS LIST --}}
    <div class="space-y-6">
        @foreach ($this->comments as $comment)
            <div class="flex gap-4" wire:key="comment-{{ $comment->id }}">
                <a href="{{ route('profile.show', $comment->user) }}" wire:navigate class="w-11 h-11 mt-3 rounded-full border-2 border-[#0f0f0f] bg-[#0f0f0f] text-white shadow-sm overflow-hidden flex items-center justify-center font-bold text-xs uppercase select-none shrink-0">
                    @if ($comment->user->avatar)
                        <img src="{{ asset('storage/' . $comment->user->avatar) }}" alt="user_image" class="w-full h-full object-cover">
                    @else
                        <span>{{ Str::upper(Str::substr($comment->user->name, 0, 2)) }}</span>
                    @endif
                </a>

                <div class="flex-1">
                    <div class="bg-[#f0f0f0] rounded-2xl px-5 py-3">
                        <div class="w-full flex justify-between mb-2">
                            <h4 class="font-black text-gray-900 text-sm">{{ $comment->user->name }}</h4>
                            <p class="text-xs font-extrabold text-gray-600">{{ $comment->created_at->diffForHumans() }}</p>
                        </div>
                        <p class="text-sm text-gray-700 leading-7">{{ $comment->body }}</p>
                    </div>

                    <div x-data="{ activeReply: null, showReplies: false }">
                        <div class="flex items-center gap-5 mt-3 ml-2 text-xs font-bold text-gray-500">
                            <button @click="activeReply = activeReply === {{ $comment->id }} ? null : {{ $comment->id }}" class="hover:text-black transition">Reply</button>

                            @if ($comment->replies->count() > 0)
                                <button x-show="!showReplies" @click="showReplies = !showReplies" class="hover:text-black transition">View {{ $comment->replies->count() }} more reply</button>
                            @endif
                        </div>

                        <div x-show="showReplies" class="py-4 pl-4 border-l border-gray-200 mt-2 space-y-4" x-cloak>
                            @foreach ($comment->replies as $reply)
                                <div class="flex gap-4" wire:key="reply-{{ $reply->id }}">
                                    <a href="{{ route('profile.show', $reply->user) }}" wire:navigate>
                                        <div class="w-11 h-11 mt-3 rounded-full object-cover border-2 border-[#0f0f0f] shadow-sm overflow-hidden flex items-center justify-center bg-[#0f0f0f] text-white text-xs font-bold uppercase">
                                            @if ($reply->user->avatar)
                                                <img src="{{ asset('storage/' . $reply->user->avatar) }}" alt="user_image" class="w-full h-full object-cover">
                                            @else
                                                <span>{{ Str::upper(Str::substr($reply->user->name, 0, 2)) }}</span>
                                            @endif
                                        </div>
                                    </a>

                                    <div class="flex-1">
                                        <div class="bg-[#f0f0f0] rounded-2xl px-5 py-2 my-2">
                                            <div class="w-full flex justify-between mb-1">
                                                <h4 class="font-black text-gray-900 text-sm">{{ $reply->user->name }}</h4>
                                                <p class="text-xs font-extrabold text-gray-600">{{ $reply->created_at->diffForHumans() }}</p>
                                            </div>
                                            <p class="text-sm text-gray-700 leading-7">{{ $reply->body }}</p>
                                        </div>

                                        <div class="flex items-center gap-5 ml-2 text-xs font-bold text-gray-500">
                                            <button @click="activeReply = activeReply === {{ $reply->id }} ? null : {{ $reply->id }}" class="hover:text-black transition">Reply</button>
                                        </div>

                                        <div x-show="activeReply === {{ $reply->id }}" class="mt-2" x-cloak>
                                            <form wire:submit.prevent="postReply({{ $comment->id }})">
                                                <div class="w-full flex items-end gap-4">
                                                    <div class="flex-1">
                                                        <input type="text" wire:model="replybody" placeholder="Reply to {{ $reply->user->name }}..." class="w-full border border-gray-200 rounded-xl p-2 text-sm focus:outline-none focus:ring-1 focus:ring-black">
                                                    </div>
                                                    <button type="submit" class="bg-black hover:bg-gray-900 text-white px-6 h-10 rounded-xl text-sm font-black transition whitespace-nowrap">Comment</button>
                                                </div>
                                                @error('replybody') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div x-show="activeReply === {{ $comment->id }}" class="mt-3" x-cloak>
                            <form wire:submit.prevent="postReply({{ $comment->id }})">
                                <div class="w-full flex items-end gap-4">
                                    <div class="flex-1">
                                        <input type="text" wire:model="replybody" placeholder="Reply to {{ $comment->user->name }}..." class="w-full border border-gray-200 rounded-xl p-2 text-sm focus:outline-none focus:ring-1 focus:ring-black">
                                    </div>
                                    <button type="submit" class="bg-black hover:bg-gray-900 text-white px-6 h-10 rounded-xl text-sm font-black transition whitespace-nowrap">Comment</button>
                                </div>
                                @error('replybody') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
