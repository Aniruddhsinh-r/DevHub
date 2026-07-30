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
<div class="min-h-screen bg-gray-200 flex flex-col justify-center items-center px-6 py-12">
    <div class="text-center max-w-md w-full">
        <!-- Icon Badge -->
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-indigo-50 border border-indigo-100 text-indigo-600 mb-6 shadow-sm">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
        </div>

        <h1 class="text-6xl font-black text-gray-900 tracking-tight mb-2">404</h1>
        <h2 class="text-xl font-bold text-gray-800 tracking-tight mb-3">Page Not Found</h2>

        <p class="text-sm text-gray-500 leading-relaxed mb-8">
            The page you are trying to reach might have been deleted, renamed, or temporarily went offline. Let's get you back on track.
        </p>

        <a href="{{ route('filament.app.pages.home') }}" class="inline-flex justify-center items-center px-6 py-3 border border-transparent text-sm font-semibold rounded-xl text-white bg-indigo-600 hover:bg-indigo-700 shadow-lg shadow-indigo-100 transition-all duration-150">
            Go Back Home
        </a>
    </div>
</div>
</body>
</html>
