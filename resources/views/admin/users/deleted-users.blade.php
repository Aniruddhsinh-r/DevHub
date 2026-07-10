<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <span class="text-[11px] font-black uppercase tracking-[0.25em] text-red-500">Trash</span>
            <h1 class="text-2xl font-black text-[#111827] mt-2 tracking-tight">Deleted Users</h1>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.users') }}" wire:navigate
                class="inline-flex items-center gap-2 h-[44px] px-5 rounded-2xl bg-[#111827] hover:bg-gray-800 text-sm font-black text-white shadow-sm">
                Users
            </a>

            <div class="bg-white border border-gray-200 rounded-2xl px-4 py-2 shadow-md flex items-baseline gap-2">
                <span class="text-xs text-gray-400 font-bold uppercase tracking-wider">Deleted:</span>
                <span class="text-lg font-black">{{ $users->total() }}</span>
            </div>
        </div>
    </div>

    <div class="bg-white border border-gray-200 rounded-[2rem] shadow-sm overflow-hidden">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 px-5 py-5 border-b border-gray-100">
            <div>
                <h2 class="text-lg font-black text-[#111827]">Recover Deleted Accounts</h2>
                <p class="text-sm text-gray-500 mt-1">Restore deleted users or permanently remove them.</p>
            </div>

            <div class="relative w-full lg:w-64">
                <form wire:submit.prevent="">
                    <div class="relative">
                        <input type="text" wire:model.live="search" value="{{ request('search') }}" placeholder="Search deleted users..." class="w-full h-11 rounded-2xl border border-gray-200 bg-[#fafafa] pl-4 pr-4 text-sm font-medium text-gray-700 outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition">
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

        @if($users->count())
            <table class="w-full">
                <thead class="bg-[#fafafa] border-b border-gray-100">
                <tr>
                    <th class="px-6 py-4 text-left text-[11px] font-black uppercase tracking-[0.2em] text-gray-400">User</th>
                    <th class="px-6 py-4 text-left text-[11px] font-black uppercase tracking-[0.2em] text-gray-400">Role</th>
                    <th class="px-6 py-4 text-left text-[11px] font-black uppercase tracking-[0.2em] text-gray-400">Deleted At</th>
                    <th class="px-6 py-4 text-right text-[11px] font-black uppercase tracking-[0.2em] text-gray-400">Action</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                @foreach($users as $user)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                @if($user->avatar)
                                    <img src="{{ asset('storage/'.$user->avatar) }}" class="w-11 h-11 rounded-full object-cover">
                                @else
                                    <div class="w-11 h-11 rounded-full bg-black text-white flex items-center justify-center font-black">
                                        {{ substr($user->name,0,2) }}
                                    </div>
                                @endif
                                <div>
                                    <div class="font-bold">{{ $user->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $user->email }}</div>
                                </div>
                            </div>
                        </td>

                        <td class="px-6 py-4">{{ ucfirst($user->getRoleNames()->first()) }}</td>

                        <td class="px-6 py-4">{{ $user->deleted_at?->format('d M Y h:i A') }}</td>

                        <td class="px-6 py-4 text-right">
                            <div class="flex justify-end gap-2">
                                <button wire:click="restore({{ $user->id }})" data-test="RestoreUser"
                                    class="h-8 px-4 rounded-xl cursor-pointer bg-green-100 hover:bg-green-600 text-green-700 hover:text-white text-xs font-bold">
                                    Restore
                                </button>

                                <button x-on:click="$dispatch('open-delete', { id: {{ $user->id }}, title: '{{ addslashes($user->name) }}', type: 'adminUserDelete' })"
                                    class="h-8 px-4 rounded-xl cursor-pointer bg-red-100 hover:bg-red-600 text-red-700 hover:text-white text-xs font-bold">
                                    Delete Forever
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <div class="p-5">{{ $users->links() }}</div>
        @else
            <div class="p-16 text-center">
                <h2 class="text-xl font-black">Trash is Empty</h2>
                <p class="text-gray-500 mt-2">There are no deleted users.</p>
            </div>
        @endif
    </div>
</div>
{{-- 210 --}}
