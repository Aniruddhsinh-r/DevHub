<?php

use App\Models\Article;
use App\Models\Comment;
use App\Notifications\CommentNotification;
use Livewire\Attributes\Computed;
use App\Enums\ArticleStatus;
use Livewire\Attributes\Validate;
use Livewire\Component;
use App\Events\ArticleCreate;
use App\Enums\UserRole;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    public Article $article;
    public string $body = '';
    public array $replybody = [];

    #[Computed]
    public function comments()
    {
        return $this->article->comments()->whereNull('parent_id')->with(['user', 'replies.user', 'replies.replies'])->latest()->get();
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
        ArticleCreate::dispatch();
        $this->body = '';
        $this->dispatch('live-notification', message: 'Comment successfully posted.');
    }

    public function postReply($parentId) {
        $this->validate([
            "replybody.{$parentId}" => 'required|string|max:5000'
        ], [
            "replybody.{$parentId}.required" => 'The reply field cannot be empty.'
        ]);

        if (!auth()->user()?->hasRole(UserRole::AUTHOR)) {
            session()->flash('error', 'Only Author can reply on comments.');
            return $this->redirectRoute('/', navigate: true);
        }

        $parent = Comment::with('user')->where('article_id', $this->article->id)->find($parentId);

        if (!$parent) {
            unset($this->replybody[$parentId]);
            $this->dispatch('live-notification', message: 'This comment no longer exists.');
            return;
        }

        $comment = Comment::create([
            'user_id' => Auth::id(),
            'article_id' => $this->article->id,
            'parent_id' => $parent->id,
            'body' => $this->replybody[$parentId],
        ]);

        $parent = Comment::with('user')->find($parentId);
        if ($parent && $parent->user_id !== Auth::id()) {
            $parent->user->notify(new CommentNotification($comment));
        }

        ArticleCreate::dispatch();
        unset($this->replybody[$parentId]);
        $this->dispatch('live-notification', message: 'Reply successfully posted.');
    }
};
?>

<div class="mt-10 bg-zinc-900 rounded-[2rem] border border-zinc-800 shadow-xl p-6 md:p-8 text-zinc-200">
    <!-- Header -->
    <div class="flex items-center justify-between mb-8">
        <div>
            <h3 class="text-2xl font-black tracking-tight text-zinc-100">Comments</h3>
            <p class="text-sm text-zinc-400 mt-1">Join the discussion</p>
        </div>
        <span class="bg-zinc-800 text-zinc-300 border border-zinc-700/60 px-4 py-2 rounded-full text-xs font-black">
            {{ $article->comments->count() }} Comments
        </span>
    </div>

    <!-- Main Comment Form -->
    <div class="flex gap-4 mb-8">
        <div class="w-11 h-11 rounded-full bg-zinc-800 border border-zinc-700/70 flex items-center justify-center text-sm font-black uppercase overflow-hidden shrink-0 text-zinc-300">
            @if (auth()->user()?->avatar)
                <img src="{{ asset('storage/' . auth()->user()?->avatar) }}" alt="{{ auth()->user()?->name }}" class="w-11 h-11 object-cover">
            @else
                {{ substr(auth()->user()?->name, 0, 2) }}
            @endif
        </div>
        <div class="flex-1">
            <form wire:submit.prevent="postComment">
                <div class="w-full">
                    <textarea wire:model="body" name="body" placeholder="Start your story..." class="w-full bg-zinc-800/80 border border-zinc-700/70 rounded-2xl p-4 text-sm text-zinc-200 placeholder-zinc-500 focus:outline-none focus:ring-2 focus:ring-zinc-400 transition"></textarea>
                </div>
                @error('body') <div class="text-xs mt-1 text-red-400 font-medium">{{ $message }}</div> @enderror

                <div class="flex justify-end mt-3">
                    <button type="submit" class="bg-zinc-200 hover:bg-zinc-300 text-zinc-900 px-6 py-3 rounded-xl text-sm font-black transition">
                        <span wire:loading wire:target="postComment">commenting...</span>
                        <span wire:loading.remove wire:target="postComment">Comment</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Comments List -->
    <div class="space-y-6">
        @foreach ($this->comments as $comment)
            <div class="flex gap-4 mt-2" wire:key="node-l1-{{ $comment->id }}" x-data="{ activeReplyId: null, showReplies: true }">
                <!-- User Avatar -->
                <a href="{{ route('profile.show', $comment->user) }}" wire:navigate class="w-11 h-11 mt-1 rounded-full border border-zinc-700/80 bg-zinc-800 text-zinc-200 flex items-center justify-center font-bold text-xs uppercase overflow-hidden shrink-0">
                    @if ($comment->user->avatar) <img src="{{ asset('storage/' . $comment->user->avatar) }}" class="w-full h-full object-cover"> @else <span>{{ Str::upper(Str::substr($comment->user->name, 0, 2)) }}</span> @endif
                </a>
                
                <div class="flex-1">
                    <!-- Comment Bubble -->
                    <div class="bg-zinc-800/70 border border-zinc-700/50 rounded-2xl px-5 py-3">
                        <h4 class="font-black text-zinc-100 text-sm mb-1">{{ $comment->user->name }}</h4>
                        <p class="text-sm text-zinc-300 leading-7">{{ $comment->body }}</p>
                    </div>
                    
                    <!-- Actions -->
                    <div class="flex items-center gap-5 mt-2 ml-2 text-xs font-bold text-zinc-400">
                        <button @click="activeReplyId = activeReplyId === 'l1-{{ $comment->id }}' ? null : 'l1-{{ $comment->id }}'" class="hover:text-zinc-100 transition">Reply</button>
                        @if ($comment->replies->count() > 0)
                            <button @click="showReplies = !showReplies" class="hover:text-zinc-100 transition">View/Hide Replies ({{ $comment->replies->count() }})</button>
                        @endif
                    </div>

                    <!-- Level 1 Reply Box -->
                    <div x-show="activeReplyId === 'l1-{{ $comment->id }}'" class="mt-3" x-cloak>
                        <form wire:submit.prevent="postReply({{ $comment->id }})">
                            <div class="w-full flex items-end gap-4">
                                <input type="text" wire:model="replybody.{{ $comment->id }}" placeholder="Reply to {{ $comment->user->name }}..." class="flex-1 bg-zinc-800 border border-zinc-700/70 rounded-xl p-2 text-sm text-zinc-200 placeholder-zinc-500 focus:outline-none focus:ring-1 focus:ring-zinc-400">
                                <button type="submit" class="bg-zinc-200 text-zinc-900 px-6 h-10 rounded-xl text-sm font-black hover:bg-zinc-300 transition">Reply</button>
                            </div>
                            @error("replybody.{$comment->id}") <p class="text-xs mt-1 text-red-400 font-medium">{{ $message }}</p> @enderror
                        </form>
                    </div>

                    <!-- Level 2 Replies Container -->
                    <div x-show="showReplies" class="pl-6 border-l-2 border-zinc-800 mt-4 space-y-4" x-cloak>
                        @foreach ($comment->replies as $replyL2)
                            <div class="flex gap-4 mt-3" wire:key="node-l2-{{ $replyL2->id }}">
                                <a href="{{ route('profile.show', $replyL2->user) }}" class="w-9 h-9 rounded-full border border-zinc-700/80 bg-zinc-800 text-zinc-200 flex items-center justify-center font-bold text-xs uppercase overflow-hidden shrink-0">
                                    @if ($replyL2->user->avatar) <img src="{{ asset('storage/' . $replyL2->user->avatar) }}" class="w-full h-full object-cover"> @else <span>{{ Str::upper(Str::substr($replyL2->user->name, 0, 2)) }}</span> @endif
                                </a>

                                <div class="flex-1">
                                    <div class="bg-zinc-800/70 border border-zinc-700/50 rounded-2xl px-5 py-3">
                                        <h4 class="font-black text-zinc-100 text-sm mb-1">{{ $replyL2->user->name }}</h4>
                                        <p class="text-sm text-zinc-300 leading-7">{{ $replyL2->body }}</p>
                                    </div>
                                    
                                    <div class="flex items-center gap-5 mt-2 ml-2 text-xs font-bold text-zinc-400">
                                        <button @click="activeReplyId = activeReplyId === 'l2-{{ $replyL2->id }}' ? null : 'l2-{{ $replyL2->id }}'" class="hover:text-zinc-100 transition">Reply</button>
                                    </div>

                                    <!-- Level 2 Reply Box -->
                                    <div x-show="activeReplyId === 'l2-{{ $replyL2->id }}'" class="mt-3" x-cloak>
                                        <form wire:submit.prevent="postReply({{ $replyL2->id }})">
                                            <div class="w-full flex items-end gap-4">
                                                <input type="text" wire:model="replybody.{{ $replyL2->id }}" placeholder="Reply to {{ $replyL2->user->name }}..." class="flex-1 bg-zinc-800 border border-zinc-700/70 rounded-xl p-2 text-sm text-zinc-200 placeholder-zinc-500 focus:outline-none focus:ring-1 focus:ring-zinc-400">
                                                <button type="submit" class="bg-zinc-200 text-zinc-900 px-6 h-10 rounded-xl text-sm font-black hover:bg-zinc-300 transition">Reply</button>
                                            </div>
                                            @error("replybody.{$replyL2->id}") <p class="text-xs mt-1 text-red-400 font-medium">{{ $message }}</p> @enderror
                                        </form>
                                    </div>

                                    <!-- Level 3 Replies Container -->
                                    <div class="pl-6 border-l-2 border-zinc-800 mt-4 space-y-4">
                                        @foreach ($replyL2->replies as $replyL3)
                                            <div class="flex gap-4 mt-3 pt-2 border-t border-dashed border-zinc-800 first:border-0 first:pt-0" wire:key="node-l3-{{ $replyL3->id }}">
                                                <a href="{{ route('profile.show', $replyL3->user) }}" class="w-9 h-9 rounded-full border border-zinc-700/80 bg-zinc-800 text-zinc-200 flex items-center justify-center font-bold text-xs uppercase overflow-hidden shrink-0">
                                                    @if ($replyL3->user->avatar) <img src="{{ asset('storage/' . $replyL3->user->avatar) }}" class="w-full h-full object-cover"> @else <span>{{ Str::upper(Str::substr($replyL3->user->name, 0, 2)) }}</span> @endif
                                                </a>

                                                <div class="flex-1">
                                                    <div class="bg-zinc-800/70 border border-zinc-700/50 rounded-2xl px-5 py-3">
                                                        <div class="flex items-center gap-2 mb-1">
                                                            <h4 class="font-black text-zinc-100 text-sm">{{ $replyL3->user->name }}</h4>

                                                            @if($replyL3->parent_id !== $replyL2->id && $replyL3->parent)
                                                                <span class="text-xs text-zinc-400 font-bold flex items-center gap-1">
                                                                    ↳ <span class="text-zinc-300 underline">@ {{ $replyL3->parent->user->name }}</span>
                                                                </span>
                                                            @endif
                                                        </div>
                                                        <p class="text-sm text-zinc-300 leading-7">{{ $replyL3->body }}</p>
                                                    </div>
                                                    
                                                    <div class="flex items-center gap-5 mt-2 ml-2 text-xs font-bold text-zinc-400">
                                                        <button @click="activeReplyId = activeReplyId === 'l3-{{ $replyL3->id }}' ? null : 'l3-{{ $replyL3->id }}'" class="hover:text-zinc-100 transition">Reply</button>
                                                    </div>

                                                    <!-- Level 3 Reply Box -->
                                                    <div x-show="activeReplyId === 'l3-{{ $replyL3->id }}'" class="mt-3" x-cloak>
                                                        <form wire:submit.prevent="postReply({{ $replyL2->id }})">
                                                            <div class="w-full flex items-end gap-4">
                                                                <input type="text" wire:model="replybody.{{ $replyL2->id }}" placeholder="Reply to {{ $replyL3->user->name }}..." class="flex-1 bg-zinc-800 border border-zinc-700/70 rounded-xl p-2 text-sm text-zinc-200 placeholder-zinc-500 focus:outline-none focus:ring-1 focus:ring-zinc-400">
                                                                <button type="submit" class="bg-zinc-200 text-zinc-900 px-6 h-10 rounded-xl text-sm font-black hover:bg-zinc-300 transition">Reply</button>
                                                            </div>
                                                            @error("replybody.{$replyL2->id}") <p class="text-xs mt-1 text-red-400 font-medium">{{ $message }}</p> @enderror
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>