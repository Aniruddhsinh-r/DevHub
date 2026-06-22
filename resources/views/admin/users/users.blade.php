
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <span class="text-[11px] font-black uppercase tracking-[0.25em] text-indigo-500">User Management</span>
                <h1 class="text-2xl font-black text-[#111827] mt-2 tracking-tight">Platform Users</h1>
            </div>

            <div class="flex items-center gap-3">
                <div class="bg-white border border-gray-200 rounded-2xl px-4 py-2 shadow-sm">
                    <span class="text-xs text-gray-400 font-bold uppercase tracking-wider block">Total Users</span>
                    <span class="text-lg font-black text-[#111827]">{{ $users->count() }}</span>
                </div>
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-[2rem] shadow-sm overflow-hidden">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 px-5 py-5 border-b border-gray-100">
                <div>
                    <h2 class="text-lg font-black text-[#111827] tracking-tight">All Registered Users</h2>
                    <p class="text-sm text-gray-500 mt-1">Manage your community members and user accounts.</p>
                </div>
                <div class="relative w-full lg:w-64">
                    <form action="" method="GET">
                        <div class="relative">
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search users..." class="w-full h-11 rounded-2xl border border-gray-200 bg-[#fafafa] pl-4 pr-4 text-sm font-medium text-gray-700 outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition">

                            @if (request('search'))
                                <a href="{{ url()->current() }}" wire:navigate class="absolute right-11 top-1/2 -translate-y-1/2 w-5 h-5 rounded-full bg-gray-200 hover:bg-black hover:text-white text-gray-600 flex items-center justify-center transition-all duration-200">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </a>
                            @endif

                            <button type="submit" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-black transition-all duration-200">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m1.85-5.15a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-full">
                    <thead class="bg-[#fafafa] border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-4 text-left text-[11px] font-black uppercase tracking-[0.2em] text-gray-400">User</th>
                            <th class="px-6 py-4 text-left text-[11px] font-black uppercase tracking-[0.2em] text-gray-400">Role</th>
                            <th class="px-6 py-4 text-left text-[11px] font-black uppercase tracking-[0.2em] text-gray-400">Joined</th>
                            <th class="px-6 py-4 text-right text-[11px] font-black uppercase tracking-[0.2em] text-gray-400">Action</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100">
                        @foreach ($users as $user)
                            <tr class="hover:bg-gray-50/70 transition-all duration-200">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3 min-w-0">
                                        @if ($user->avatar)
                                            <img src="{{ asset('storage/' . $user->avatar) }}" alt="{{ $user->name }}" class="w-11 h-11 rounded-full object-cover border border-gray-200 shrink-0">
                                        @else
                                            <div class="w-11 h-11 rounded-full bg-black text-white flex items-center justify-center text-sm font-black uppercase shrink-0">{{ substr($user->name, 0, 2) }}</div>
                                        @endif
                                        <div class="min-w-0">
                                            <h3 class="text-sm font-bold text-[#111827] truncate">
                                                {{ $user->name }}
                                            </h3>
                                            <p class="text-xs text-gray-500 truncate mt-0.5">
                                                {{ $user->email }}
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-[11px] font-bold
                                        {{ $user->hasRole('admin')
                                            ? 'bg-indigo-100 text-indigo-600'
                                            : 'bg-gray-100 text-gray-600' }}">
                                        {{ ucfirst($user->getRoleNames()->first()) }}
                                    </span>
                                </td>

                                <td class="px-6 py-4 text-sm font-medium text-gray-500">{{ \Carbon\Carbon::parse($user->created_at)->format('d M Y') }}</td>

                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.show.user',$user) }}" wire:navigate class="inline-flex items-center justify-center h-8 px-4 rounded-xl border border-gray-200 bg-white hover:bg-[#111827] text-xs font-bold text-gray-700 hover:text-white transition-all duration-200 shadow-sm hover:shadow-md">
                                            View
                                        </a>

                                        <form wire:submit.prevent="remove({{ $user->id }})">
                                            @csrf
                                            <button class="inline-flex items-center justify-center h-8 px-4 rounded-xl bg-rose-50 hover:bg-red-600 text-xs font-bold text-red-600 hover:text-white transition-all duration-200 shadow-sm hover:shadow-md">
                                                Remove
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="m-5 font-semibold">
                    {{ $users->appends(['search' => request('search')])->links() }}
                </div>
            </div>
        </div>
    </div>
