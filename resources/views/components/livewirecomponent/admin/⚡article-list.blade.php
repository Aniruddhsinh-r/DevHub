<?php

use Livewire\Component;
use App\Models\Article;
use App\Enums\ArticleStatus;
use Livewire\WithPagination;
use Livewire\Attributes\On;
use App\Events\ArticleCreate;
use App\Actions\ArticleDelete;
use Livewire\Attributes\Layout;

new #[Layout('layouts::dashboard')] class extends Component
{
    use WithPagination;
    public $search = '';
    public $selectAll = false;
    public $selectedArticles = [];

    public function mount() {
        $this->search = request()->query('search', '');
    }

    #[On('echo:articles,ArticleCreate')]
    public function refreshArticlesList()
    {
        $this->dispatch('$refresh');
    }

    public function render()
    {
        return view('admin.articles.articles', [
            'articles' => Article::query()->with(['user','category'])
                ->when($this->search, function ($query, $search) {
                    $query->where(function ($query) use ($search) {
                        $query->where('title', 'like', "%{$search}%")
                            ->orWhere('excerpt', 'like', "%{$search}%")
                            ->orWhere('body', 'like', "%{$search}%");
                    });
                })
                ->latest()
                ->paginate(9),
        ]);
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            // Get all current page article SLUGS instead of IDs
            $this->selectedArticles = Article::query()
                ->when($this->search, function ($query, $search) {
                    $query->where(function ($query) use ($search) {
                        $query->where('title', 'like', "%{$search}%")
                            ->orWhere('excerpt', 'like', "%{$search}%")
                            ->orWhere('body', 'like', "%{$search}%");
                    });
                })
                ->latest()
                ->paginate(9)
                ->pluck('id') // Changed from 'id' to 'slug'
                ->map(fn($id) => (string)$id)
                ->toArray();
        } else {
            $this->selectedArticles = [];
        }
    }
    
    #[On('trigger-delete')]
    public function handleGlobalDelete($id, $type, ArticleDelete $articleDelete) {
        if ($type === 'adminArticleBulkDelete') {
            if (!empty($id)) {
                $articles = Article::whereIn('id', $id)->get();
                foreach ($articles as $article) {
                    $articleDelete->handle($article);
                }
                $this->selectedArticles = [];
                ArticleCreate::dispatch();
                $this->dispatch('live-notification', message: 'Selected articles deleted successfully.');
            }
        }

        if ($type === 'adminArticleDelete') {
            $this->remove($id);
        }
    }

    public function remove($articleId) {
        $article = Article::findOrFail($articleId);
        $action = app(ArticleDelete::class);
        $action->handle($article);
        ArticleCreate::dispatch();

        session()->flash('success', 'Article deleted successfully.');
        return $this->redirectRoute('admin.articles', navigate: true);
    }
};
?>
