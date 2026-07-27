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
        <div class="max-w-2xl text-center">
            <h1 class="mt-8 text-8xl font-black tracking-tight text-amber-400">410</h1>
            <h2 class="mt-6 text-4xl font-bold">Invitation Expired</h2>
            <p class="mt-6 text-lg leading-8 text-zinc-400">
                This invitation link has expired or cancelled by the administrator.
                Invitation links are available for a limited time to help keep your account secure.
            </p>
            <p class="mt-3 text-zinc-500">Please contact your administrator to request a new invitation.</p>
            <div class="mt-10 flex flex-col items-center justify-center gap-4 sm:flex-row">
                <a href="{{ route('filament.app.pages.home') }}" wire:navigate class="inline-flex items-center rounded-2xl bg-white px-6 py-3 font-bold text-black transition hover:scale-105">Go Home</a>
                <a href="{{ route('login') }}" wire:navigate class="inline-flex items-center rounded-2xl border border-zinc-700 px-6 py-3 font-bold text-white transition hover:border-zinc-500 hover:bg-zinc-900">Login</a>
            </div>
        </div>
    </div>
</body>
</html>