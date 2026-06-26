    <div class="space-y-6 max-w-7xl mx-auto">
        <div>
            <h1 class="text-2xl font-black text-gray-900 tracking-tight">Category Management</h1>
            <p class="text-sm text-gray-500 mt-1">Create new article categories and manage existing ones.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
            <div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-[0_8px_30px_rgb(0,0,0,0.02)]">
                <h2 class="text-sm font-bold text-gray-900 tracking-tight mb-4 flex items-center gap-2">
                    <span class="w-2.5 h-2.5 bg-indigo-600 rounded-full"></span>Create New Category
                </h2>

                <form wire:submit.prevent="create" class="space-y-4" autocomplete="off">
                    @csrf
                    <x-form.field name="name" type="text" label="Category Name" placeholder="e.g., Technology, Helth, Coding..."></x-form.field>

                    <button type="submit" data-test="CreateCategory" class="w-full py-2.5 px-4 bg-[#111827] text-white hover:bg-gray-800 font-semibold text-sm rounded-xl shadow-sm transition-all duration-200 mt-2 flex items-center justify-center gap-2">Create Category</button>
                </form>
            </div>

            <div class="lg:col-span-2 bg-white border border-gray-100 rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.02)] overflow-hidden">
                <div class="p-5 border-b border-gray-50">
                    <h2 class="text-sm font-bold text-gray-900 tracking-tight">Existing Categories</h2>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-gray-100 bg-gray-50/70">
                                <th class="text-[10px] font-bold text-gray-400 uppercase tracking-widest px-5 py-3">Category Name</th>
                                <th class="text-[10px] font-bold text-gray-400 uppercase tracking-widest px-5 py-3">slug</th>
                                <th class="text-[10px] font-bold text-gray-400 uppercase tracking-widest px-5 py-3">user</th>
                                <th class="text-[10px] font-bold text-gray-400 uppercase tracking-widest px-5 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach ($categories as $category)
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="px-5 py-3.5 text-sm font-bold text-gray-900">{{ $category->name }}</td>
                                    <td class="px-5 py-3.5 text-sm font-bold text-gray-900">{{ $category->slug }}</td>
                                    <td class="px-5 py-3.5 text-xs text-gray-500 font-mono">{{ $category->articles_count }}</td>
                                    <td class="px-5 py-3.5 text-right">
                                        <button wire:click="remove({{ $category->id }})" dusk="delete-category-{{ $category->id }}"
                                             wire:confirm.prompt="Are you sure?\n\nType DELETE to confirm|DELETE" class="text-xs font-bold text-rose-600 hover:text-rose-800 transition-colors">Delete
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="m-5 font-semibold">
                    {{ $categories->links() }}
                </div>
            </div>
        </div>
    </div>
