<?php

use Livewire\Component;
use App\Models\Article;
use App\Models\Category;
use Illuminate\Support\Str;
use Livewire\WithFileUploads;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

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
        $this->scheduled_hours = $article->scheduled_hours;
        $this->scheduled_minutes = $article->scheduled_minutes;
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

    public function update() {
        Gate::authorize('update', $this->article);

        $values = $this->validate([
            'title' => 'required|max:255|min:6',
            'excerpt' => 'required|max:80|min:10',
            'body' => 'required|min:30|max:50000',
            'category_id' => 'required|exists:categories,id',
            'status' => ['required', Rule::in(['draft', 'scheduled', 'published'])],
            'scheduled_hours' => 'nullable|integer|min:1|max:48',
            'scheduled_minutes' => 'required_if:status,scheduled|nullable|integer|min:0|max:59',
            'cover_path' => 'nullable|dimensions:max_width=1000,max_height=1000|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $article = $this->article;

        $data = collect($values)->only([
            'title', 'excerpt', 'body', 'category_id','status',
        ])->toArray();

        if (($values['title'] ?? null) && $values['title'] !== $this->article->title) {
            $base = Str::slug($values['title'], '-');
            $slug = $base;
            $count = 2;

            while (Article::where('slug', $slug)->where('id', '!=', $this->article->id)->exists()) {
                $slug = $base . '-' . $count;
                $count++;
            }

            $data['slug'] = $slug;
        }

        if ($this->cover_path) {
            $data['cover_path'] = $values['cover_path']->store('articleCovers', 'public');
            if ($article->cover_path) {
                Storage::disk('public')->delete($article->cover_path);
            }
        }

        if ($values['status'] === 'scheduled' && !empty($values['scheduled_minutes'])) {
            $data['published_at'] = now()->addHours((int)($values['scheduled_hours'] ?? 0))->addMinutes((int)($values['scheduled_minutes'] ?? 0));
        } elseif ($values['status'] === 'published') {
            $data['published_at'] = now();
        }

        $this->article->update($data);

        session()->flash('success', 'Article updated successfully.');
        return $this->redirectRoute('publishedarticle', navigate: true);
    }
};
?>

<div>
    @include('articles.articleForm')
</div>
