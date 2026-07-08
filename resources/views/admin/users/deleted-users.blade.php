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
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search deleted users..."
                class="w-full h-11 rounded-2xl border border-gray-200 bg-[#fafafa] px-4 text-sm">
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
                                <button wire:click="restore({{ $user->id }})"
                                    class="h-8 px-4 rounded-xl bg-green-100 hover:bg-green-600 text-green-700 hover:text-white text-xs font-bold">
                                    Restore
                                </button>

                                <button x-on:click="$dispatch('open-delete', { id: {{ $user->id }}, title: '{{ addslashes($user->name) }}', type: 'adminUserDelete' })"
                                    class="h-8 px-4 rounded-xl bg-red-100 hover:bg-red-600 text-red-700 hover:text-white text-xs font-bold">
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
