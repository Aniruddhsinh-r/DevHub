<?php

use Livewire\Component;
use App\Models\Article;
use App\Actions\ArticleDelete;
use Illuminate\Support\Facades\Gate;

new class extends Component
{
    public Article $article;

    public function mount(Article $article)
    {
        $this->article = $article;
        Gate::authorize('delete', $this->article);
    }

    public function delete(ArticleDelete $action) {
        Gate::authorize('delete', $this->article);
        
        $action->handle($this->article);

        session()->flash('success', 'Article deleted successfully.');
        return $this->redirectRoute('publishedarticle', navigate: true);
    }
};
?>

<div>
    <button wire:click="delete" class="text-xs font-bold text-red-500 hover:text-red-700 uppercase tracking-wider" dusk="delete-article-{{ $article->id }}">Delete</button>
</div>
