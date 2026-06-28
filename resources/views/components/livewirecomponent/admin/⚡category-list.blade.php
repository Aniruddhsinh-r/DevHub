<?php

use Livewire\Component;
use App\Models\Category;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Gate;

new #[Layout('layouts::dashboard')] class extends Component
{
    use WithPagination;
    #[Validate('required|min:3|max:20|string|unique:categories,name')]
    public $name = '';

    public function render() {
        return view('admin.categories', [
            'categories' => Category::withCount('articles')->latest()->paginate(6),
        ]);
    }

    public function create() {
        Gate::authorize('create', Category::class);

        $this->validate();

        $slug = Str::slug($this->name, '-');

        if (Category::where('slug', $slug)->exists()) {
            $this->addError('name', 'A category with a similar name already exists in the system.');
            return;
        }

        Category::create([
            'name' => $this->name,
            'slug' => $slug,
            'created_at' => now(),
        ]);

        $this->reset(['name']);
        $this->dispatch('live-notification', message: 'Category created successfully.');
    }

    #[On('trigger-delete')]
    public function handleGlobalDelete($id, $type) {
        if ($type === 'adminCategory') {
            $this->remove($id);
        }
    }

    public function remove($categoryId) {
        $category = Category::findOrFail($categoryId);
        Gate::authorize('delete', $category);

        $category->delete();
        $this->dispatch('live-notification', message: 'Category deleted successfully.');
    }
};
?>
