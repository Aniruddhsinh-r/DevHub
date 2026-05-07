<x-layout>
    <div class="min-h-screen py-12 px-4 sm:px-6">

        <div class="max-w-2xl mx-auto">

            <div class="mb-8 border-l-4 border-black pl-5">
                <h2 class="text-2xl font-black uppercase tracking-tighter text-gray-900">Create Article</h2>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-[0.2em]">Editor Workspace</p>
            </div>

            <div class="bg-[#c6caca] rounded-4xl border border-gray-100 shadow-sm overflow-hidden">
                <form method="POST" action="{{ route('createArticle')}}" class="p-8 md:p-10 space-y-6" enctype="multipart/form-data">
                    @csrf

                    <x-form.field name="title" type="text" label="Headline" placeholder="Enter a captivating title..."></x-form.field>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-700">Collection</label>
                            <select name="category_id" class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl text-sm focus:ring-1 focus:ring-black outline-none transition-all">
                                <option value="" disabled selected>Select Category</option>
                                <option value="1" selected>Laravel</option>
                                <option value="2">PHP</option>
                                <option value="3">React</option>
                                {{-- @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach --}}
                            </select>
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-700">Visibility</label>
                            <select name="status" class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl text-sm focus:ring-1 focus:ring-black outline-none transition-all">
                                <option value="draft">Save as Draft</option>
                                <option value="published">Publish Now</option>
                            </select>
                        </div>
                    </div>

                    <x-form.field name="excerpt" type="text" label="Excerpt" placeholder="Briefly describe the article..."></x-form.field>

                    <x-form.field name="body" type="textarea" label="Story Body" placeholder="Start your story..."></x-form.field>

                    <div class="space-y-0.5">
                        <label for="avtar" class="block font-medium">cover picture</label>
                        <input type="file" id="avtar" name="avtar" class="border border-gray-400 w-full font-medium text-sm text-gray-700 rounded-md shadow-xs cursor-pointer file:bg-black file:text-white file:px-4 file:py-2 file:rounded-l-md file:border-0" placeholder="Profile Pic" />
                    </div>

                    <div class="pt-6 border-t border-gray-200 flex justify-end">
                        <button type="submit" class="w-full md:w-auto bg-black text-white px-10 py-3.5 rounded-full text-[11px] font-black uppercase tracking-[0.2em] hover:bg-gray-800 transition-all active:scale-95 shadow-lg shadow-black/10">
                            Create Article
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</x-layout>
