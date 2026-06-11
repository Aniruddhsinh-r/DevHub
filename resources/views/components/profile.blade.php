<x-layout>
    <div class="min-h-screen px-4 py-10">
        <div class="max-w-6xl mx-auto space-y-6">
            <div class="bg-[#fcfcfb] border border-gray-200 rounded-[1.5rem] shadow-sm p-6">
                <div class="grid grid-cols-1 lg:grid-cols-[220px_1fr_300px] gap-8 items-center">
                    <div class="flex justify-center lg:justify-start">
                    @if ($user->avtar)
                        <img src="{{ asset('storage/' . $user->avtar) }}" alt="{{ $user->name }}" class="w-56 h-56 rounded-full object-cover border-4 border-[#f3f3f1] shadow-sm">
                        {{-- <img src="{{ asset('storage/' . $user->avtar) }}" class="w-32 h-32 rounded-full object-cover border-4 border-[#f3f3f1] shadow-sm"> --}}
                    @else
                        <div class="w-56 h-56 rounded-full bg-[#ececea] flex items-center justify-center text-6xl font-black text-[#111111] border-4 border-[#f3f3f1]">
                            {{ substr($user->name, 0, 2) }}
                        </div>
                    @endif
                    </div>

                    <div class="space-y-6">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">{{ $user->name }}</h1>
                            <p class="text-sm text-gray-600 mt-1 capitalize">{{ $user->role }} Fullstack Web Developer</p>
                        </div>

                        <p class="text-gray-600 leading-6 text-sm max-w-xl">{{ $user->bio }}</p>
                        <div class="flex items-center gap-6 pt-1">
                            <a href="{{ route('followings',['user' => $user]) }}">
                                <h2 class="text-xl font-bold text-gray-900">{{ number_format($user->following()->count()) }}</h2>
                                <p class="text-gray-500 text-sm mt-1">Following</p>
                            </a>
                            <div class="w-px h-10 bg-gray-300"></div>
                            <a href="{{ route('followers',['user' => $user]) }}">
                                <h2 class="text-xl font-bold text-gray-900">{{ number_format($user->followers()->count()) }}</h2>
                                <p class="text-gray-500 text-sm mt-1">Followers</p>
                            </a>
                        </div>
                    </div>

                    <div class="border-l border-gray-200 pl-5 flex flex-col justify-between h-full">
                        <div class="space-y-6">
                            <div class="flex items-center justify-between gap-4">
                                <span class="text-gray-500 font-medium">Role</span>
                                <span class="text-gray-900 font-semibold capitalize">{{ $user->role }}</span>
                            </div>

                            <div class="flex items-center justify-between gap-4">
                                <span class="text-gray-500 font-medium">Last Seen</span>
                                <span class="text-gray-900 font-semibold">{{ $user->last_seen_at ? $user->last_seen_at->diffForHumans() : 'Recently Active' }}</span>
                            </div>

                            <div class="flex items-center justify-between gap-4">
                                <span class="text-gray-500 font-medium">Joined At</span>
                                <span class="text-gray-900 font-semibold">{{ $user->created_at->format('M d, Y') }}</span>
                            </div>

                            <div class="flex items-center justify-between gap-4">
                                <span class="text-gray-500 font-medium">Total Articles</span>
                                <span class="text-gray-900 font-semibold">{{ $articles->count() }}</span>
                            </div>
                        </div>
                        @role('author')
                            <form action="{{ route('user.follow',$user) }}" method="post">
                                @csrf
                                @if (auth()->user()->following->contains($user->id))
                                    <button class="mt-6 bg-blue-600 hover:bg-blue-800 transition text-white font-semibold py-2.5 rounded-xl text-sm w-full">Unfollow</button>
                                @else
                                    <button class="mt-6 bg-blue-600 hover:bg-blue-800 transition text-white font-semibold py-2.5 rounded-xl text-sm w-full">Follow</button>
                                @endif
                            </form>
                        @endrole
                    </div>
                </div>
            </div>

            <div class="bg-[#fcfcfb] border border-gray-200 rounded-[2rem] shadow-sm p-8">
                <div class="flex items-center justify-between mb-5">
                    <h2 class="text-xl font-bold text-gray-900">
                        {{ auth()->id() === $user->id ? 'My Articles' : $user->name . "'s Articles" }}
                    </h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
                    @forelse ($articles->take(3) as $article)
                        <a href='{{ route('articles.show',$article) }}' class="p-5 bg-gray-200 rounded-2xl">
                            <h3 class="text-xl line-clamp-1 font-black leading-tight tracking-tight text-gray-800">{{ $article->title }}</h3>
                            <p class="mt-2 h-10 text-gray-600 text-sm leading-relaxed line-clamp-2">{{ $article->excerpt }}</p>

                            <div class="mt-5 flex items-center justify-between border-t border-gray-100">
                                <div>
                                    <p class="text-[10px] uppercase tracking-[0.18em] font-bold text-gray-400">Published</p>
                                    <p class="text-xs font-semibold text-gray-700 mt-0.5">{{ $article->created_at->diffForHumans() }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-[10px] uppercase tracking-[0.18em] font-bold text-gray-400">Date</p>
                                    <p class="text-xs font-semibold text-gray-700 mt-0.5">{{ $article->created_at->format('d M Y') }}</p>
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="col-span-1 md:col-span-2 xl:col-span-3 flex flex-col items-center justify-center p-12 bg-gray-50 border border-dashed border-gray-300 rounded-2xl text-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 text-gray-400 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                            </svg>
                            <p class="text-gray-600 font-medium">No articles posted yet</p>
                            <p class="text-xs text-gray-400 mt-1">Check back later to see published thoughts and ideas.</p>
                        </div>
                    @endforelse
                </div>

                @if ($articles->count() > 3)
                    <a href="{{ route('user.published',$user) }}" class="flex justify-center mt-8">
                        <button class="border border-gray-300 hover:border-black hover:bg-black hover:text-white transition px-6 py-2.5 rounded-xl text-sm font-semibold text-gray-800">Show More Articles</button>
                    </a>
                @endif
            </div>
        </div>
    </div>
</x-layout>
