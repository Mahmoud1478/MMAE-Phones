<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Phones Workbench' }}</title>
    @vite(['workbench/resources/css/app.css', 'workbench/resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-gray-50 text-gray-900 antialiased">
    <nav class="border-b border-gray-200 bg-white">
        <div class="mx-auto flex max-w-5xl items-center gap-6 px-6 py-3">
            <span class="font-bold tracking-tight">mmae/phones</span>
            <a href="{{route('users.index')}}" wire:navigate
                class="text-sm font-medium text-gray-600 hover:text-indigo-600">Users</a>
            <a href="{{route('phones.checker')}}" wire:navigate
                class="text-sm font-medium text-gray-600 hover:text-indigo-600">Phone Checker</a>
        </div>
    </nav>

    {{ $slot }}

    @livewireScripts
</body>
</html>
