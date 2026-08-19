<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        body { font-family: 'Inter', sans-serif; }
        .glass-effect { background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(10px); }
    </style>
</head>
<body class="bg-[#f8f9fa] text-[#1a1a1a]">
<div class="min-h-screen bg-zinc-950 text-white flex items-center justify-center px-6">
    <div class="max-w-xl text-center">
        <h1 class="text-8xl font-black text-yellow-400 mb-6">500</h1>
        <h2 class="text-3xl font-bold mb-4">Internal Server Error</h2>

        <p class="text-zinc-400 text-lg mb-8">Something went wrong on our server.</p>

        <a href="{{ auth()->user()?->hasAnyRole(['admin', 'superadmin']) ? route('filament.admin.pages.dashboard') : route('filament.app.pages.home') }}"
           class="inline-flex items-center px-6 py-3 rounded-2xl bg-yellow-400 text-black font-bold hover:scale-105 transition">
            Return Home
        </a>
    </div>
</div>
</body>
</html>
