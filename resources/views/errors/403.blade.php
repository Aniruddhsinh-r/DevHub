<x-layout>
<div class="min-h-screen bg-black text-white flex items-center justify-center px-6">
    <div class="max-w-xl text-center">
        <h1 class="text-8xl font-black text-red-500 mb-6">403</h1>
        <h2 class="text-3xl font-bold mb-4">Access Forbidden</h2>

        <p class="text-zinc-400 text-lg mb-8">You don't have permission to access this page.</p>
        <a href="{{ route('home') }}" wire:navigate class="inline-flex items-center px-6 py-3 rounded-2xl bg-white text-black font-bold hover:scale-105 transition">Go Home</a>
    </div>
</div>
</x-layout>
