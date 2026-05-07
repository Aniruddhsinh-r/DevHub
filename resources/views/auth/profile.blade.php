<x-layout>
    <div class="py-12 bg-slate-50 min-h-screen">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl border border-slate-200/60">

                <div class="h-32 bg-linear-to-r from-slate-100 to-gray-100 border-b border-slate-200/60 flex items-end px-6 sm:px-10 pb-4">
                    <div class="text-xs text-slate-400 font-semibold tracking-wider uppercase">User Profile</div>
                </div>

                <div class="relative px-6 sm:px-10 pb-10">

                    <div class="flex flex-col sm:flex-row items-start sm:items-end -mt-10 mb-8 space-y-4 sm:space-y-0 sm:space-x-6">

                        <div class="relative">
                            @if ($user->avtar)
                                <img src="{{ asset('storage/' . $user->avtar) }}" alt="Profile Picture" class="w-32 h-32 rounded-2xl object-cover border-4 border-white shadow-sm ring-1 ring-slate-200/60">
                            @else
                                <div class="w-32 h-32 rounded-2xl bg-slate-100 flex items-center justify-center border-4 border-white shadow-sm ring-1 ring-slate-200/60 text-slate-700 font-bold text-3xl">
                                    {{ substr($user->name, 0, 2) }}
                                </div>
                            @endif
                            <span class="absolute bottom-2 right-2 w-4 h-4 bg-emerald-500 rounded-full border-2 border-white"></span>
                        </div>

                        <div class="flex-1">
                            <h1 class="text-3xl font-bold text-slate-900 tracking-tight">{{ $user->name }}</h1>
                            <p class="text-sm text-slate-500 mt-1">Email: {{ $user->email }}</p>

                            <div class="mt-3 flex items-center space-x-2">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-slate-100 text-slate-800 border border-slate-200">
                                    {{ $user->role }}
                                </span>
                            </div>
                        </div>

                        <div class="w-full sm:w-auto">
                            <a href="#" class="w-full inline-flex justify-center items-center px-5 py-2.5 border border-slate-300 shadow-sm text-sm font-medium rounded-xl text-slate-700 bg-white hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-500 transition-colors duration-200">
                                <svg class="w-5 h-5 mr-2 -ml-1 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                                Edit Profile
                            </a>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 pt-8 border-t border-slate-100">

                        <div class="md:col-span-2 space-y-6">

                            <div>
                                <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Contact Information</h3>
                                <div class="mt-3 bg-slate-50/50 rounded-xl p-4 border border-slate-200/60">
                                    <p class="text-xs text-slate-400 font-medium">Email Address</p>
                                    <p class="text-sm font-semibold text-slate-900 mt-0.5">{{ $user->email }}</p>
                                </div>
                            </div>

                            <div>
                                <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Biography</h3>
                                <div class="mt-3 bg-slate-50/50 rounded-xl p-4 border border-slate-200/60">
                                    <p class="text-xs text-slate-400 font-medium mb-1">About Me</p>
                                    <p class="text-sm text-slate-700 leading-relaxed">
                                        {{ $user->bio ?? 'No biography provided yet. Click "Edit Profile" to add information about yourself.' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-slate-50/50 rounded-2xl p-5 border border-slate-200/60 flex flex-col justify-between">
                            <div>
                                <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-4">Account Details</h3>

                                <div class="space-y-4">
                                    <div class="flex justify-between items-center text-sm border-b border-slate-200/50 pb-3">
                                        <span class="text-slate-500 text-xs font-medium">User ID</span>
                                        <span class="font-semibold text-slate-900 text-sm">#{{ $user->id }}</span>
                                    </div>

                                    <div class="flex justify-between items-center text-sm border-b border-slate-200/50 pb-3">
                                        <span class="text-slate-500 text-xs font-medium">Joined Date</span>
                                        <span class="font-medium text-slate-900 text-sm">
                                            {{ $user->created_at ? $user->created_at->format('M d, Y') : 'N/A' }}
                                        </span>
                                    </div>

                                    <div class="flex justify-between items-center text-sm border-b border-slate-200/50 pb-3">
                                        <span class="text-slate-500 text-xs font-medium">Status</span>
                                        <span class="inline-flex items-center text-emerald-600 font-semibold text-xs bg-emerald-50 px-2.5 py-0.5 rounded-full">
                                            Active
                                        </span>
                                    </div>

                                    <div class="flex justify-between items-center text-sm border-b border-slate-200/50 pb-3">
                                        <span class="text-slate-500 text-xs font-medium">Last Active</span>
                                        <span class="font-medium text-slate-900 text-xs">
                                            {{ $user->last_seen_at ? $user->last_seen_at->diffForHumans() : 'Just now' }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-10 pt-4 border-t border-slate-200/50 text-center text-xs text-slate-400 italic">
                                Member since {{ $user->created_at ? $user->created_at->diffForHumans() : '' }}
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layout>

