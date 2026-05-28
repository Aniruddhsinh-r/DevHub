<x-layout>
    <div class="min-h-screen bg-gray-50 py-12 px-4">
        <div class="max-w-7xl mx-auto">
            <div class="flex flex-col md:flex-row md:items-center justify-between mb-10 border-b border-gray-200 pb-8">
                <div>
                    <h1 class="text-3xl font-black text-gray-900 tracking-tight">Your Publications</h1>
                    <p class="text-gray-500 mt-2 font-medium">Manage and monitor the performance of your shared content.</p>
                </div>
                <div class="mt-4 md:mt-0">
                    <a href="{{ route('drafts',auth()->user()->id) }}" class="px-6 py-3 rounded-2xl bg-[#111111] text-gray-200 text-sm font-black tracking-wide hover:bg-black transition-all duration-300">
                        draft
                    </a>
                </div>
            </div>

            <!-- Articles Grid -->
            @if($articles->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach($articles as $article)
                        <div class="group bg-white rounded-[20px] overflow-hidden border border-gray-200 shadow-sm hover:shadow-2xl hover:-translate-y-1 transition-all duration-500 relative">
                            <a href="{{ route('articles.show',$article->id) }}" class="block relative overflow-hidden h-52">
                                @if ($article->cover_path)
                                    <img src="{{ asset('storage/' . $article->cover_path) }}" alt="{{ $article->title }}" class="w-full h-52 object-cover group-hover:scale-105 transition-transform duration-700">
                                @endif
                                <div class="absolute top-4 right-4 bg-black/75 backdrop-blur-md text-white text-xs font-semibold px-4 py-2 rounded-full">{{ $article->view_count }} Views</div>
                            </a>

                            <div class="p-4 pt-3">
                                <a href="{{ route('articles.show',$article->id) }}">
                                    <h3 class="text-xl line-clamp-1 font-black leading-tight tracking-tight text-gray-800 hover:text-gray-600">{{ $article->title }}</h3>
                                    <p class="mt-2 h-10 text-gray-600 text-sm leading-relaxed line-clamp-2">{{ $article->excerpt }}</p>
                                </a>

                                <div class="mt-5 flex items-center justify-between border-t border-gray-50 pt-4">
                                    <div>
                                        <p class="text-[10px] uppercase tracking-[0.18em] font-bold text-gray-400">{{ $article->status }}</p>
                                        <p class="text-xs font-semibold text-gray-700 mt-0.5">{{ $article->created_at->diffForHumans() }}</p>
                                    </div>

                                    <div class="flex flex-col items-end gap-1">
                                        <form action="{{ route('articles.destroy',$article->id) }}" method="post" onsubmit="return confirm('Are you sure?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="text-xs font-bold text-red-500 hover:text-red-700 uppercase tracking-wider">Delete</button>
                                        </form>

                                        <a href="{{ route('articles.edit',$article->id) }}" class="text-xs font-bold text-gray-900 hover:text-black uppercase tracking-wider">
                                            Edit
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else

                <!-- Empty State -->
                <div class=" bg-white border border-gray-200 rounded-[3rem] p-20 text-center shadow-sm">
                    <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                    <h2 class="text-2xl font-black text-gray-900">No articles yet</h2>
                    <p class="text-gray-500 mt-2 font-medium">You haven't published any content to your profile.</p>
                    <a href="{{ route('articles.create') }}" class="inline-block mt-8 px-8 py-4 bg-gray-900 text-white text-xs font-black uppercase tracking-widest rounded-2xl hover:bg-black transition-all">
                        Write Your First Story
                    </a>
                </div>
            @endif
        </div>
    </div>
</x-layout>
