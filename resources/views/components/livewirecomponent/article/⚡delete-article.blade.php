<?php

use Livewire\Component;
use App\Models\Article;
use App\Models\View;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

new class extends Component
{
    public Article $article;

    public function mount(Article $article)
    {
        $this->article = $article;
        Gate::authorize('delete', $this->article);
    }

    public function delete() {
        Gate::authorize('delete', $this->article);

        $this->deleteArticle();
    }

    protected function deleteArticle()
    {
        if ($this->article->cover_path) {
            Storage::disk('public')->delete($this->article->cover_path);
        }
        DB::transaction(function () {
            $this->article->bookmarks()->detach();
            $this->article->delete();
        });

        session()->flash('success', 'Article deleted successfully.');
        return $this->redirectRoute('publishedarticle', navigate: true);
    }
};
?>

<div>
    <button wire:click="delete" class="text-xs font-bold text-red-500 hover:text-red-700 uppercase tracking-wider" dusk="delete-article-{{ $article->id }}">Delete</button>
</div>
