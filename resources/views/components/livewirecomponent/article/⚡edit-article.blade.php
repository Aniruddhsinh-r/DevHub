<?php

use Livewire\Component;
use App\Models\Article;
use App\Actions\UpdateArticle;
use App\Models\Category;
use Livewire\WithFileUploads;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Gate;

new class extends Component
{
    use WithFileUploads;

    public Article $article;
    public $categories;

    public function mount(Article $article) {
        $this->article = $article;
        Gate::authorize('update', $this->article);

        $this->title = $article->title;
        $this->excerpt = $article->excerpt;
        $this->body = $article->body;
        $this->category_id = $article->category_id;
        $this->status = $article->status;
        $diff = ($article->published_at && $article->status === 'scheduled') ? now()->diffInMinutes($article->published_at, false) : 0;
        $this->scheduled_hours = $diff > 0 ? floor($diff / 60) : 0;
        $this->scheduled_minutes = $diff > 0 ? ($diff % 60) : 0;
        $this->categories = Category::select('id','name')->orderBy('name')->get();
    }

    public $title;
    public $excerpt;
    public $body;
    public $category_id;
    public $status;
    public $scheduled_hours;
    public $scheduled_minutes;
    public $cover_path;

    public function update(UpdateArticle $action) {
        Gate::authorize('update', $this->article);

        $values = $this->validate([
            'title' => 'required|max:255|min:6',
            'excerpt' => 'required|max:80|min:10',
            'body' => 'required|min:30|max:50000',
            'category_id' => 'required|exists:categories,id',
            'status' => ['required', Rule::in(['draft', 'scheduled', 'published'])],
            'scheduled_hours' => 'nullable|integer|min:0|max:48',
            'scheduled_minutes' => 'required_if:status,scheduled|nullable|integer|min:0|max:59',
            'cover_path' => 'nullable|dimensions:max_width=1000,max_height=1000|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $article = $this->article;

        $action->handle($values, $article);

        session()->flash('success', 'Article updated successfully.');
        return $this->redirectRoute('publishedarticle', navigate: true);
    }
};
?>

<div>
    @include('articles.articleForm')
</div>
