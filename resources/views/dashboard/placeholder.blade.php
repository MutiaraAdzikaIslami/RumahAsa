<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RumahAsa ? Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="h-full">
    <div class="min-h-screen flex items-center justify-center bg-slate-50 px-4">
        <div class="max-w-md w-full bg-white rounded-2xl shadow-lg p-8 text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-emerald-100 mb-4">
                <svg class="w-8 h-8 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 mb-2">Login Berhasil!</h1>
            <p class="text-slate-600 mb-6">Selamat datang, <strong>{{ $user->nama }}</strong></p>
            <div class="bg-slate-50 rounded-lg p-4 mb-6 text-left text-sm">
                <p class="text-slate-500 mb-1">Role:</p>
                <p class="font-semibold text-slate-900">{{ $user->role->nama }}</p>
                <p class="text-slate-500 mt-3 mb-1">Status scrum-10:</p>
                <p class="font-mono text-xs text-emerald-600">Login & session berfungsi</p>
            </div>
            <form method="POST" action="{{ route('logout') }}" class="inline">
                @csrf
                <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition">
                    Logout
                </button>
            </form>
        </div>
    </div>
</body>
</html>
