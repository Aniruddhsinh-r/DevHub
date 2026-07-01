<?php

use Livewire\Component;
use App\Enums\ArticleStatus;
use App\Models\Article;
use Livewire\Attributes\Layout;
use App\Models\Category;
use Livewire\WithFileUploads;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Validate;
use Livewire\Attributes\On;
use App\Events\CategoryCreated;
use App\Actions\UpdateArticle;

new #[Layout('layouts::dashboard')] class extends Component
{
    use WithFileUploads;

    public Article $article;
    public $categories;

    public $delete_cover = false;

    public function mount(Article $article) {
        $this->article = $article;

        $this->title = $article->title;
        $this->excerpt = $article->excerpt;
        $this->body = $article->body;
        $this->category_id = $article->category_id;
        $this->status = $article->status;
        $diff = ($article->published_at && $article->status === ArticleStatus::SCHEDULED) ? now()->diffInMinutes($article->published_at, false) : 0;
        $this->scheduled_hours = $diff > 0 ? floor($diff / 60) : 0;
        $this->scheduled_minutes = $diff > 0 ? ($diff % 60) : 0;
        $this->categories = Category::select('id','name')->orderBy('name')->get();
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

    #[Validate('required|min:6|max:255')]
    public $title;
    #[Validate('required|min:10|max:80')]
    public $excerpt;
    #[Validate('required|min:30|max:50000')]
    public $body;
    #[Validate('required|exists:categories,id')]
    public $category_id;
    #[Validate(['required', new \Illuminate\Validation\Rules\Enum(ArticleStatus::class)])]
    public $status;
    #[Validate('nullable|integer|min:0|max:48')]
    public $scheduled_hours;
    #[Validate('required_if:status,scheduled|nullable|integer|min:0|max:59')]
    public $scheduled_minutes;
    #[Validate('nullable|image|mimes:jpeg,png,jpg,webp|max:2048')]

    public $cover_path;

    public function removeCover() {
        $this->cover_path = null;
        $this->delete_cover = true;
    }

    public function update(UpdateArticle $action) {
        $values = $this->validate();

        $article = $this->article;
        $values['delete_cover'] = $this->delete_cover;

        $action->handle($values, $article);

        session()->flash('success', 'Article updated successfully.');
        $this->cover_path = null;
        return $this->redirectRoute('admin.articles', navigate: true);
    }
};
?>

<div>
    @include('articles.articleForm')
</div>
