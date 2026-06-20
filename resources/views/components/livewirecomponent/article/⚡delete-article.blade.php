<?php

use Livewire\Component;
use App\Models\Article;
use App\Models\View;
use Illuminate\Support\Facades\Gate;

new class extends Component
{
    public Article $article;

    public function mount() {
        Gate::authorize('delete', $this->article);

        if ($this->article->cover_path) {
            Storage::disk('public')->delete($this->article->cover_path);
        }
        $this->article->likes()->delete();
        $this->article->comments()->delete();
        $this->article->bookmarks()->detach();
        View::where('article_id', $this->article->id)->delete();
        $this->article->delete();

        session()->flash('success', 'Article deleted successfully.');
        return redirect()->route('publishedarticle');
    }
};
?>
