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
<body class="bg-[#f8f9fa] text-[#1a1a1a] min-h-screen flex flex-col">

        <div class="text-black">
            {{ $slot }}
        </div>

    <script>
        window.onload = function () {
            const userId = "{{ auth()->id() }}";
            if (userId && window.Echo) {
                window.Echo.private(`App.Models.User.${userId}`)
                    .notification((notification) => {
                        window.dispatchEvent(new CustomEvent('live-notification', {
                            detail: { message: notification.message,}
                        }));
                    });
            }
        };
    </script>
    @livewireScripts
</body>
</html>