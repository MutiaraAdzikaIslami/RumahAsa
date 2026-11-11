@extends('layouts.guest')

@section('title', 'Ganti Password')

@section('content')
<div class="bg-white rounded-2xl shadow-xl p-8 border border-slate-200" x-data="passwordForm()">
    <div class="text-center mb-6">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-emerald-100 mb-4">
            <svg class="w-8 h-8 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-6 6m-3-6a6 6 0 01-6-6M9 7a2 2 0 00-2 2" />
            </svg>
        </div>
        <h1 class="text-2xl font-bold text-slate-900">Ganti Password</h1>
        <p class="text-sm text-slate-500 mt-1">Jaga keamanan akun Anda</p>
    </div>

    @if (session('warning'))
        <div class="mb-4 p-3 rounded-lg bg-amber-50 border border-amber-200 text-sm text-amber-700">
            <strong>Wajib ganti password:</strong> {{ session('warning') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 p-3 rounded-lg bg-red-50 border border-red-200 text-sm text-red-700">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('password.update') }}" class="space-y-5">
        @csrf
        @method('PUT')

        <div>
            <label for="current_password" class="block text-sm font-medium text-slate-700 mb-1">Password Lama</label>
            <input type="password" id="current_password" name="current_password" required class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 outline-none transition" placeholder="Masukkan password lama">
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-slate-700 mb-1">Password Baru</label>
            <input type="password" id="password" name="password" required x-model="newPassword" @input="checkStrength()" class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 outline-none transition" placeholder="Min. 8 karakter, huruf + angka">
            <div class="mt-2 flex items-center gap-2">
                <div class="flex-1 h-1.5 bg-slate-200 rounded-full overflow-hidden">
                    <div class="h-full transition-all duration-300" :class="strengthColor" :style="`width: ${strengthPercent}%`"></div>
                </div>
                <span class="text-xs font-medium text-slate-600" x-text="strengthLabel"></span>
            </div>
            <p class="mt-1 text-xs text-slate-500">Hanya huruf dan angka, tanpa simbol/spasi.</p>
        </div>

        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-slate-700 mb-1">Konfirmasi Password Baru</label>
            <input type="password" id="password_confirmation" name="password_confirmation" required x-model="confirmPassword" class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 outline-none transition" :class="confirmPassword && confirmPassword !== newPassword ? 'border-red-300' : (confirmPassword && confirmPassword === newPassword ? 'border-emerald-300' : '')" placeholder="Ulangi password baru">
            <p x-show="confirmPassword && confirmPassword !== newPassword" x-cloak class="mt-1 text-xs text-red-600">Konfirmasi tidak cocok.</p>
            <p x-show="confirmPassword && confirmPassword === newPassword" x-cloak class="mt-1 text-xs text-emerald-600">Konfirmasi cocok.</p>
        </div>

        <button type="submit" :disabled="!canSubmit" class="w-full py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 disabled:bg-slate-300 disabled:cursor-not-allowed text-white font-medium rounded-lg transition focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
            Ganti Password
        </button>
    </form>

    @if (!$mustChange)
    <div class="mt-6 text-center">
        <a href="{{ route('dashboard') }}" class="text-sm text-slate-500 hover:text-slate-700">Kembali ke Dashboard</a>
    </div>
    @endif
</div>

<style>[x-cloak] { display: none !important; }</style>

<script>
function passwordForm() {
    return {
        newPassword: '',
        confirmPassword: '',
        strengthPercent: 0,
        strengthLabel: '',
        strengthColor: 'bg-slate-300',

        get canSubmit() {
            return this.newPassword.length >= 8
                && this.confirmPassword === this.newPassword
                && /^[A-Za-z0-9]+$/.test(this.newPassword);
        },

        checkStrength() {
            const pwd = this.newPassword;
            if (pwd.length === 0) {
                this.strengthPercent = 0;
                this.strengthLabel = '';
                this.strengthColor = 'bg-slate-300';
                return;
            }

            let score = 0;
            if (pwd.length >= 8) score += 25;
            if (pwd.length >= 12) score += 15;
            if (/[a-z]/.test(pwd) && /[A-Z]/.test(pwd)) score += 30;
            if (/\d/.test(pwd)) score += 30;

            this.strengthPercent = Math.min(score, 100);

            if (score < 40) {
                this.strengthLabel = 'Lemah';
                this.strengthColor = 'bg-red-500';
            } else if (score < 70) {
                this.strengthLabel = 'Sedang';
                this.strengthColor = 'bg-amber-500';
            } else {
                this.strengthLabel = 'Kuat';
                this.strengthColor = 'bg-emerald-500';
            }
        },
    };
}
</script>
@endsection