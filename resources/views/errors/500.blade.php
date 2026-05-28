<x-layout>
<div class="min-h-screen bg-zinc-950 text-white flex items-center justify-center px-6">

    <div class="max-w-xl text-center">

        <h1 class="text-8xl font-black text-yellow-400 mb-6">
            500
        </h1>

        <h2 class="text-3xl font-bold mb-4">
            Internal Server Error
        </h2>

        <p class="text-zinc-400 text-lg mb-8">
            Something went wrong on our server.
        </p>

        <a href="{{ route('home') }}"
           class="inline-flex items-center px-6 py-3 rounded-2xl bg-yellow-400 text-black font-bold hover:scale-105 transition">
            Return Home
        </a>
    </div>
</div>
</x-layout>
