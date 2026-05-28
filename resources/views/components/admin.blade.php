<x-layout>
{{-- <div class="flex overflow-hidden h-screen bg-[#f3f4f6] text-[#111827]"> --}}
<div class="flex overflow-hidden bg-[#f3f4f6] text-[#111827]" style="height: calc(100vh - 65px);">
    <aside class="w-14 sm:w-20 md:w-56 sticky top-16 bg-[#e5e7eb] border-r border-gray-300 flex flex-col justify-between p-2 sm:p-4 transition-all duration-300 shrink-0">
        <div>
            <div class="hidden md:flex items-center gap-3 mb-5 border-b pb-4 border-gray-300">
                <div class="min-w-0 flex-1">
                    <span class="text-sm font-semibold text-gray-900 leading-none">{{ auth()->user()->name }}</span>
                    <span class="text-xs text-gray-500 block mt-0.5">{{ auth()->user()->email }}</span>
                </div>
            </div>

            <nav class="flex flex-col gap-1">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 p-2.5 rounded-lg cursor-pointer transition-all duration-200 md:justify-start sm:justify-center justify-center {{ request()->is('admin/dashboard') ? 'bg-white text-gray-900 font-semibold shadow-sm' : 'text-gray-600 hover:bg-white/50 hover:text-gray-900 font-medium' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24" class="w-5 h-5 text-gray-800">
                        <path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/>
                    </svg>
                    <span class="text-sm md:block sm:hidden hidden">Dashboard</span>
                </a>

                <a href="{{ route('admin.users') }}" class="flex items-center gap-3 p-2.5 rounded-lg cursor-pointer transition-all duration-200 md:justify-start sm:justify-center justify-center {{ request()->is('admin/user*') ? 'bg-white text-gray-900 font-semibold shadow-sm' : 'text-gray-600 hover:bg-white/50 hover:text-gray-900 font-medium' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5 text-gray-500">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                    </svg>
                    <span class="text-sm md:block sm:hidden hidden">Users</span>
                </a>

                <a href="{{ route('admin.articles') }}" class="flex items-center gap-3 p-2.5 rounded-lg cursor-pointer transition-all duration-200 md:justify-start sm:justify-center justify-center {{ request()->is('admin/articles*') ? 'bg-white text-gray-900 font-semibold shadow-sm' : 'text-gray-600 hover:bg-white/50 hover:text-gray-900 font-medium' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5 text-gray-500">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                    </svg>
                    <span class="text-sm md:block sm:hidden hidden">Articles</span>
                </a>

                <a href="{{ route('admin.categories') }}" class="flex items-center gap-3 p-2.5 rounded-lg cursor-pointer transition-all duration-200 md:justify-start sm:justify-center justify-center {{ request()->is('admin/categories') ? 'bg-white text-gray-900 font-semibold shadow-sm' : 'text-gray-600 hover:bg-white/50 hover:text-gray-900 font-medium' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5 text-gray-500">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z" />
                    </svg>
                    <span class="text-sm md:block sm:hidden hidden">Categories</span>
                </a>
            </nav>
        </div>
    </aside>

    <div class="flex-1 overflow-y-auto p-4">
        {{ $slot }}
    </div>
</div>
</x-layout>
