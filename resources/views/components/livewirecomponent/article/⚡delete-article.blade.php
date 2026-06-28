<?php

use Livewire\Component;
use App\Models\Article;
use App\Actions\ArticleDelete;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Gate;

new class extends Component
{
    public Article $article;

    public function mount(Article $article)
    {
        $this->article = $article;
        Gate::authorize('delete', $this->article);
    }

    #[On('trigger-delete')]
    public function handleGlobalDelete($id, $type) {
        if ($type === 'article') {
            $this->remove($id);
        }
    }

    public function remove($articleId) {
        Gate::authorize('delete', $this->article);
        $action = app(ArticleDelete::class);
        $action->handle($this->article);

        session()->flash('success', 'Article deleted successfully.');
        return $this->redirectRoute('publishedarticle', navigate: true);
    }
};
?>

<div>
    <button type="button"
            x-on:click="$dispatch('open-delete', { id: {{ $article->id }}, title: '{{ addslashes($article->title) }}', type: 'article' })"
            class="text-xs font-bold text-red-500 hover:text-red-700 uppercase tracking-wider transition duration-150 ease-in-out"
            dusk="delete-article-{{ $article->id }}">
        Delete
    </button>
</div>
