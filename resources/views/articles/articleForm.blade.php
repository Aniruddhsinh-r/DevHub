    <div class="min-h-screen py-12 px-4 + sm:px-6">
        <div class="max-w-2xl mx-auto">
            <div class="mb-8 border-l-4 border-black pl-5">
                <h2 class="text-2xl font-black uppercase tracking-tighter text-gray-900">Create Article</h2>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-[0.2em]">Editor Workspace</p>
            </div>

            <div x-data="{ status: '{{ old('status', $article->status ?? 'draft') }}' }" class="bg-[#c6caca] rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                <form wire:submit.prevent="{{ $article->exists ? 'update' : 'store' }}" class="p-8 md:p-10 space-y-6"
                    enctype="multipart/form-data"
                    >
                    @csrf
                    @if ($article->exists)
                        @method('PATCH')
                    @endif

                    <x-form.field name="title" type="text" label="Headline" placeholder="Enter a captivating title..." :value="$article->title"></x-form.field>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-1.5">
                            <label for="category_id" class="block text-xs font-bold uppercase tracking-wider text-gray-700">Collection</label>
                            <select name="category_id" wire:model.live="category_id" class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl text-sm focus:ring-1 focus:ring-black outline-none transition-all">
                                <option disabled {{ old('category_id') ? '' : 'selected' }} value="null">Select Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id', $article->category_id ?? '') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <div class="red text-sm text-red-600">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="space-y-1.5">
                            <label for="status" class="block text-xs font-bold uppercase tracking-wider text-gray-700">Visibility</label>
                            <select x-model="status" name="status" wire:model.live="status" class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl text-sm focus:ring-1 focus:ring-black outline-none transition-all">
                                <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="published" {{ old('status') == 'published' ? 'selected' : '' }}>Published</option>
                                <option value="scheduled" {{ old('status') == 'scheduled' ? 'selected' : '' }}>scheduled</option>
                            </select>
                        </div>
                    </div>
                    <div x-show="status === 'scheduled'" x-transition style="display: none;" class="space-x-1.5">
                        <div x-data="{ hours: '{{ old('scheduled_hours') ?? null }}',minutes: '{{ old('scheduled_minutes') ?? null }}' }" class="flex items-end space-x-2">
                            <div class="w-20">
                                <label class="block text-xs font-bold uppercase tracking-wider text-gray-700">Hours</label>
                                <input type="text" name="scheduled_hours" wire:model="scheduled_hours" placeholder="HH" x-model="hours" @input="hours = hours.replace(/\D/g, '').slice(0, 2); if(parseInt(hours) > 48) hours = '48'" class="border border-gray-300 w-full p-2 text-center font-semibold text-sm rounded-md focus:border-blue-50tline-none">
                            </div>
                            <span class="text-gray-400 font-bold pb-2">:</span>
                            <div class="w-20">
                                <label class="block text-xs font-bold uppercase tracking-wider text-gray-700">Mins</label>
                                <input type="text" name="scheduled_minutes" wire:model="scheduled_minutes" placeholder="MM" x-model="minutes" @input="minutes = minutes.replace(/\D/g, '').slice(0, 2); if(parseInt(minutes) > 59) minutes = '59'" class="border border-gray-300 w-full p-2 text-center font-semibold text-sm rounded-md focus:border-blue-500 outline-none">
                            </div>
                        </div>
                        @error('scheduled_minutes')
                            <div class="red text-sm text-red-600">{{ $message }}</div>
                        @enderror
                        @error('scheduled_hours')
                            <div class="red text-sm text-red-600">{{ $message }}</div>
                        @enderror
                    </div>

                    <x-form.field name="excerpt" type="text" label="Excerpt" :value="$article->excerpt" placeholder="Briefly describe the article..."></x-form.field>

                    <x-form.field name="body" type="textarea" label="Story Body" :value="$article->body" placeholder="Start your story..."></x-form.field>

                    <div x-data="{
                        imagePreview: '{{ $article->cover_path ? asset('storage/' . $article->cover_path) : '' }}',
                        fileChosen(event) {
                            const file = event.target.files[0];
                            if (file) {
                                this.imagePreview = URL.createObjectURL(file);
                            }
                        }
                    }" class="space-y-4">

                        <template x-if="imagePreview">
                            <div class="mb-2">
                                <p class="text-[10px] text-gray-500 uppercase mb-1" x-text="event => $refs.fileInput.files.length ? 'New Image Preview:' : 'Current Image:'">Current Image:</p>
                                <img :src="imagePreview" alt="Cover Preview" class="w-32 h-20 object-cover rounded-md border border-gray-300 shadow-sm">
                            </div>
                        </template>

                        <div class="space-y-0.5">
                            <label for="cover_path" class="block text-xs font-bold uppercase tracking-wider text-gray-700">Cover Picture</label>
                            <input type="file" wire:model="cover_path" id="cover_path" name="cover_path" x-ref="fileInput" @change="fileChosen" class="border border-gray-400 w-full font-medium text-sm text-gray-700 rounded-md shadow-xs cursor-pointer file:bg-black file:text-white file:px-4 file:py-2 file:rounded-l-md file:border-0" placeholder="Choose cover page"/>
                        </div>
                    </div>

                    <div class="pt-6 border-t border-gray-200 flex justify-end">
                        <button type="submit" data-test="submitBTN" class="w-full md:w-auto bg-black text-white px-10 py-3.5 rounded-full text-[11px] font-black uppercase tracking-[0.2em] hover:bg-gray-800 transition-all active:scale-95 shadow-lg shadow-black/10">
                            <span wire:loading>Processing...</span>
                            <span wire:loading.remove >{{ $article->exists ? 'Update Article' : 'Create Article' }}</span>
                        </button>
                    </div>
                    @if ($errors->any())
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-xl mb-6">
                            <ul class="list-disc list-inside space-y-1 text-sm font-medium">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </form>
            </div>
        </div>
    </div>
