<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Gate;
use App\Models\Category;

class CategoryController extends Controller
{
    public function create(Request $request)
    {
        Gate::authorize('create', Category::class);

        $data = $request->validate([
            'name' => ['required', 'string', 'min:4', 'max:20', 'unique:categories,name'],
        ]);

        $slug = Str::slug($data['name']);

        if (Category::where('slug', $slug)->exists()) {
            return response()->json([
                'message' => "A category with a similar name already exists (slug: \"{$slug}\").",
            ], 422);
        }

        $category = Category::create([
            'name'    => $data['name'],
            'slug'    => $slug,
            'user_id' => Auth::id(),
        ]);

        return response()->json([
            'message'  => 'Category created successfully.',
            'category' => $category,
        ], 201);
    }

    public function update(Request $request, Category $category)
    {
        Gate::authorize('update', $category);

        if (! $category) {
            return response()->json(['message' => 'Article not found.'], 404);
        }
        
        $data = $request->validate([
            'name' => ['required', 'string', 'min:4', 'max:20', 'unique:categories,name'],
        ]);

        $slug = Str::slug($data['name']);

        if (Category::where('slug', $slug)->exists()) {
            return response()->json([
                'message' => "A category with a similar name already exists (slug: \"{$slug}\").",
            ], 422);
        }

        $category->update([
            'name'    => $data['name'],
            'slug'    => $slug,
        ]);

        return response()->json([
            'message'  => 'Category updated successfully.',
            'category' => $category,
        ], 200);
    }

    public function delete(Category $category)
    {
        Gate::authorize('delete', $category);

        if (! $category) {
            return response()->json(['message' => 'Article not found.'], 404);
        }

        $category->delete();

        return response()->json([
            'message' => 'Article deleted successfully.',
        ], 200);
    }
    
    public function show(Request $request)
    {   
        Gate::authorize('viewAny', Category::class);

        $categories = Category::query()
            ->when($request->search, fn ($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 10));

        return response()->json($categories);
    }
}
