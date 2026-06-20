<?php

use Livewire\Component;
use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;
    public $name = '';

    public function render() {
        return view('admin.categories', [
            'categories' => Category::withCount('articles')->latest()->paginate(6),
        ]);
    }

    public function create() {
        $this->authorize('create', Category::class);

        $this->validate([
            'name' => ['required','min:3','max:20','string','unique:categories,name']
        ]);

        $slug = Str::slug($this->name, '-');

        if (Category::where('slug', $slug)->exists()) {
            $this->validate();
        }

        Category::create([
            'name' => $this->name,
            'slug' => $slug,
            'created_at' => now(),
        ]);

        $this->reset(['name']);
        $this->dispatch('live-notification', message: 'Category created successfully.');
    }

    public function remove($categoryId) {
        $category = Category::find($categoryId);
        $this->authorize('delete', $category);

        $category->delete();
        $this->dispatch('live-notification', message: 'Category deleted successfully.');
    }
};
?>
