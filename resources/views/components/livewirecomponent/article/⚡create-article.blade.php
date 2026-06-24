<?php

use Livewire\Component;
use App\Actions\CreateArticle;
use App\Models\Article;
use App\Models\Category;
use Livewire\WithFileUploads;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Gate;

new class extends Component
{
    use WithFileUploads;
    public $categories;
    public $article;

    public $title = '';
    public $excerpt = '';
    public $body = '';
    public $category_id = null;
    public $status = 'draft';
    public $scheduled_hours = null;
    public $scheduled_minutes = null;
    public $cover_path = null;

    public function mount() {
        Gate::authorize('create', Article::class);
        $this->categories = Category::select('id','name')->orderBy('name')->get();
        $this->article = new Article();
    }

    public function store(CreateArticle $action) {
        if ($this->status === 'published') {
            Gate::authorize('publish', Article::class);
        }

        $values = $this->validate([
            'title' => 'required|max:255|min:6',
            'excerpt' => 'required|max:80|min:10',
            'body' => 'required|min:30|max:50000',
            'category_id' => 'required|exists:categories,id',
            'status' => ['required', Rule::in(['draft', 'scheduled', 'published'])],
            'scheduled_hours' => 'nullable|integer|min:0|max:48',
            'scheduled_minutes' => 'required_if:status,scheduled|nullable|integer|min:0|max:59',
            'cover_path' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $action->handle($values);

        session()->flash('success', 'Article created successfully.');
        return $this->redirectRoute('articles.index', navigate: true);
    }
};
?>

<div>
    @include('articles.articleForm')
</div>
