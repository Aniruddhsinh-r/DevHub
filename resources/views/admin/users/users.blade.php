    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <span class="text-[11px] font-black uppercase tracking-[0.25em] text-indigo-500">User Management</span>
                <h1 class="text-2xl font-black text-[#111827] mt-2 tracking-tight">Platform Users</h1>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('admin.users.create') }}" wire:navigate class="inline-flex items-center gap-2 h-[44px] px-5 rounded-2xl bg-[#111827] hover:bg-gray-800 text-sm font-black text-white shadow-sm hover:shadow-lg transition-all duration-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    Create User
                </a>
                <div class="bg-white border border-gray-200 rounded-2xl px-4 py-2 shadow-md flex items-baseline gap-2">
                    <span class="text-xs text-gray-400 font-bold uppercase tracking-wider">Total Users:</span>
                    <span class="text-lg font-black text-[#111827]">{{ $users->total() }}</span>
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
                    <form wire:submit.prevent="">
                        <div class="relative">
                            <input type="text" wire:model.live="search" value="{{ request('search') }}" placeholder="Search users..." class="w-full h-11 rounded-2xl border border-gray-200 bg-[#fafafa] pl-4 pr-4 text-sm font-medium text-gray-700 outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition">

                            @if ($search)
                                <button type="button" wire:click="$set('search', '')" class="absolute right-11 top-1/2 -translate-y-1/2 w-5 h-5 rounded-full bg-gray-200 hover:bg-black hover:text-white text-gray-600 flex items-center justify-center transition-all duration-200">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
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
                @if($users->count() > 0)
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
                                        <a href="{{ route('admin.edit.user',$user) }}" wire:navigate class="inline-flex items-center justify-center h-8 px-4 rounded-xl border border-gray-200 bg-green-100 hover:bg-green-400 text-xs font-bold hover:text-white transition-all duration-200 shadow-sm hover:shadow-md">
                                            Edit
                                        </a>
                                        <button type="button"
                                            x-on:click="$dispatch('open-delete', { id: {{ $user->id }}, title: '{{ addslashes($user->name) }}', type: 'adminUserDelete' })"
                                            class="inline-flex items-center cursor-pointer justify-center h-8 px-4 rounded-xl bg-rose-50 hover:bg-red-600 text-xs font-bold text-red-600 hover:text-white transition-all duration-200 shadow-sm hover:shadow-md">
                                            Remove
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="m-5 font-semibold">
                    {{ $users->appends(['search' => request('search')])->links() }}
                </div>
                @else
                    <div class="flex flex-col items-center justify-center p-12 text-center border-2 border-dashed border-gray-100 rounded-3xl m-5 bg-[#fafafa]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-2.533-4.65l-2.21-.737a4.8 4.8 0 01-3.208-3.208l-.737-2.21a4.125 4.125 0 00-4.65-2.533 9.34 9.34 0 00-1.886-.755A4.125 4.125 0 005.516 5.3l-.738 2.209a4.8 4.8 0 01-3.208 3.208l-2.209.738A4.125 4.125 0 000 15.116a9.39 9.39 0 004.767 7.923M15 11h.008v.008H15V11zm1 4h.008v.008H16V15zm-4-4h.008v.008H12V11zm1 4h.008v.008H13V15z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18 9v3m0 0v3m0-3h3m-3 0h-3" />
                        </svg>
                        <h3 class="text-md font-black text-gray-700">No Users Found</h3>
                        <p class="text-sm text-gray-500 mt-1 max-w-xs">There are no registered accounts on the platform yet or your search criteria returned zero results.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
