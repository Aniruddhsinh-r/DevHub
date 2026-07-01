<?php

use Livewire\Component;
use App\Models\Article;
use App\Enums\ArticleStatus;
use Livewire\WithPagination;
use App\Actions\ArticleDelete;
use Livewire\Attributes\On;
use App\Events\ArticleCreate;
use Illuminate\Support\Facades\Gate;

new class extends Component
{
    use WithPagination;
    public $search = '';

    public function mount() {
        $this->search = request()->query('search', '');
    }

    #[On('echo:articles,ArticleCreate')]
    public function refreshCategoriesList()
    {
        $this->dispatch('$refresh');
    }

    public function render()
    {
        return view('articles.article', [
            'articles' => Article::query()->with(['user','category'])
                ->where('status', ArticleStatus::PUBLISHED)
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
};
?>
