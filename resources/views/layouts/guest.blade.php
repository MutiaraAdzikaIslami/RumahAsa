<!DOCTYPE html>
<html
    lang="id"
    class="h-full bg-slate-50"
>

<head>
    <meta charset="UTF-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >
    <meta
        name="csrf-token"
        content="{{ csrf_token() }}"
    >
    <title>{{ config('app.name', 'RumahAsa') }} ? @yield('title', 'Login')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script
        defer
        src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"
    ></script>
</head>

<body class="h-full">
    <div class="min-h-full flex flex-col">
        <main class="flex-1 flex items-center justify-center px-4 py-12 sm:px-6 lg:px-8">
            <div class="w-full max-w-md">
                @yield('content')
            </div>
        </main>
    </div>
    @stack('scripts')
</body>

</html>
