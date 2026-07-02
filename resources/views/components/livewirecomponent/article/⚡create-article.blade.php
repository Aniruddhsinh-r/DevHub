<?php

use Livewire\Component;
use App\Actions\CreateArticle;
use App\Models\Article;
use App\Models\Category;
use Livewire\Attributes\On;
use App\Enums\ArticleStatus;
use Livewire\WithFileUploads;
use Illuminate\Validation\Rule;
use App\Events\CategoryCreated;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Gate;

new class extends Component
{
    use WithFileUploads;
    public $article;

    #[Validate('required|min:6|max:255')]
    public $title = '';
    #[Validate('required|min:10|max:80')]
    public $excerpt = '';
    #[Validate('required|min:30|max:50000')]
    public $body = '';
    #[Validate('required|exists:categories,id')]
    public $category_id = null;
    #[Validate(['required', new \Illuminate\Validation\Rules\Enum(ArticleStatus::class)])]
    public $status = 'draft';
    #[Validate('nullable|integer|min:0|max:48')]
    public $scheduled_hours = null;
    #[Validate('required_if:status,scheduled|nullable|integer|min:0|max:59')]
    public $scheduled_minutes = null;
    #[Validate('nullable|image|mimes:jpeg,png,jpg,webp|max:2048')]
    public $cover_path = null;

    public function mount() {
        Gate::authorize('create', Article::class);
        $this->article = new Article();
    }

    #[On('echo:categories,CategoryCreated')]
    public function refreshCategoriesList()
    {
        $this->dispatch('$refresh');
    }

    public function render()
    {
        return view('articles.articleForm', [
            'categories' => Category::select('id','name')->orderBy('name')->get()
        ]);
    }

    public function store(CreateArticle $action) {
        if ($this->status === ArticleStatus::PUBLISHED->value) {
            Gate::authorize('publish', Article::class);
        }

        $values = $this->validate();

        $action->handle($values);

        session()->flash('success', 'Article created successfully.');
        return $this->redirectRoute('articles.index', navigate: true);
    }
};
?>

<div>
    @include('articles.articleForm')
</div>
