<?php

use Livewire\Component;
use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;
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
        $this->authorize('create', Article::class);
        $this->categories = Category::all();
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
            'scheduled_hours' => 'nullable|integer|min:1|max:48',
            'scheduled_minutes' => 'required_if:status,scheduled|nullable|integer|min:1|max:59',
            'cover_path' => 'nullable|dimensions:max_width=1000,max_height=1000|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $title = $values['title'];

        $data = collect($values)->only([
            'title', 'excerpt', 'body', 'category_id', 'status',
        ])->merge(['published_at' => now()])->toArray();

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

        if ($values['status'] === 'scheduled' && !empty($values['scheduled_minutes'])) {
            $data['published_at'] = now()->addHours((int)($values['scheduled_hours'] ?? 0))->addMinutes((int)($values['scheduled_minutes'] ?? 0));
        } elseif ($values['status'] === 'published') {
            $data['published_at'] = now();
        }

        auth()->user()->articles()->create($data);

        session()->flash('success', 'Article created successfully.');
        return redirect()->route('articles.index');
    }
};
?>

<div>
    @include('articles.articleForm')
</div>
