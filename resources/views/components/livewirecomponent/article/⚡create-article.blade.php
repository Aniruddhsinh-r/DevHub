<?php

use Livewire\Component;
use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Str;
use Livewire\WithFileUploads;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;

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

    public function store() {
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

        $title = $values['title'];

        $data = collect($values)->only([
            'title', 'excerpt', 'body', 'category_id', 'status',
        ])->toArray();

        $base = Str::slug($title, '-');
        $slug = $base;
        $count = 2;
        while (Article::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $count;
            $count++;
        }

        $data['slug'] = $slug;

        if ($values['cover_path'] ?? false) {
            $data['cover_path'] = $values['cover_path']->store('articleCovers','public');
        }

        if ($values['status'] === 'scheduled') {
            $data['published_at'] = now()->addHours((int)($values['scheduled_hours'] ?? 0))->addMinutes((int)($values['scheduled_minutes'] ?? 0));
        } elseif ($values['status'] === 'published') {
            $data['published_at'] = now();
        } else {
            $data['published_at'] = null; // Drafts stay null until active
        }

        auth()->user()->articles()->create($data);

        session()->flash('success', 'Article created successfully.');
        return $this->redirectRoute('articles.index', navigate: true);
    }
};
?>

<div>
    @include('articles.articleForm')
</div>
