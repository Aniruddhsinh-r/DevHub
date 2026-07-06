<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800;900&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>
<body class="bg-black text-white">

<div class="min-h-screen flex items-center justify-center px-6">
    <div class="max-w-xl text-center">
        
        <h1 class="text-8xl font-black text-violet-500 mb-6">409</h1>
        <h2 class="text-3xl font-bold mb-4">Account Already Exists</h2>

        <p class="text-zinc-400 text-lg leading-8 mb-3">An account has already been created using this invitation.</p>
        <p class="text-zinc-500 mb-10">If this is your account, sign in to continue. Otherwise, contact your administrator for assistance.</p>

        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            <a href="{{ route('login') }}" class="inline-flex items-center justify-center px-6 py-3 rounded-2xl bg-white text-black font-bold hover:scale-105 transition">Go to Login</a>
            <a href="{{ route('home') }}" class="inline-flex items-center justify-center px-6 py-3 rounded-2xl border border-zinc-700 text-white font-bold hover:bg-zinc-900 transition">Go Home</a>
        </div>
    </div>
</div>
</body>
</html>