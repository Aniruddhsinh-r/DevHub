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
    <div class="min-h-screen bg-black text-white flex items-center justify-center px-6">
        <div class="max-w-xl text-center">
            <h1 class="text-8xl font-black text-red-500 mb-6">403</h1>
            <h2 class="text-3xl font-bold mb-4">Access Forbidden</h2>

            <p class="text-zinc-400 text-lg mb-8">You don't have permission to access this page.</p>
            <a href="{{ auth()->user()?->hasRole('admin')
                ? route('filament.admin.pages.dashboard')
                : route('filament.app.pages.home') }}" class="inline-flex items-center px-6 py-3 rounded-2xl bg-white text-black font-bold hover:scale-105 transition">Go Home</a>
        </div>
    </div>
</body>
</html>
