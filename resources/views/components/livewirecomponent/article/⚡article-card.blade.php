<?php

use Livewire\Component;
use App\Models\Article;
use App\Enums\ArticleStatus;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;
    public $search = '';

    public function mount() {
        $this->search = request()->query('search', '');
    }

    public function render()
    {
        return view('articles.article', [
            'articles' => Article::query()
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
