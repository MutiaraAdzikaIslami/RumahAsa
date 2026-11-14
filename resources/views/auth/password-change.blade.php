@extends('layouts.guest')

@section('title', 'Ganti Password')

@section('content')
{{-- Bungkus form dalam max-w-md supaya tidak kebesaran --}}
<div class="w-full max-w-md mx-auto py-12">
    <div class="bg-white rounded-2xl shadow-xl p-8 border border-slate-200" x-data="passwordForm()">
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-emerald-100 mb-4">
                <i data-lucide="lock-keyhole" class="w-8 h-8 text-emerald-600"></i>
            </div>
            <h1 class="text-2xl font-bold text-slate-900">Ganti Password</h1>
            <p class="text-sm text-slate-500 mt-1">Jaga keamanan akun Anda</p>
        </div>

        @if (session('warning'))
            <div class="mb-4 p-3 rounded-lg bg-amber-50 border border-amber-200 text-sm text-amber-700 flex items-center gap-3">
                <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                <span>{{ session('warning') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 p-3 rounded-lg bg-red-50 border border-red-200 text-sm text-red-700 flex items-start gap-3">
                <i data-lucide="alert-circle" class="w-5 h-5 mt-0.5"></i>
                <div>
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('password.update') }}" class="space-y-5">
            @csrf
            @method('PUT')

            {{-- Password Lama --}}
            <div>
                <label for="current_password" class="block text-sm font-medium text-slate-700 mb-1">Password Lama</label>
                <div class="relative">
                    <input 
                        :type="showCurrent ? 'text' : 'password'" 
                        id="current_password" 
                        name="current_password" 
                        required 
                        class="w-full px-4 py-2.5 pr-10 rounded-lg border border-slate-300 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 outline-none transition"
                        placeholder="Masukkan password lama"
                    >
                    <button type="button" @click="showCurrent = !showCurrent" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">
                        <i x-show="!showCurrent" data-lucide="eye" class="w-5 h-5"></i>
                        <i x-show="showCurrent" x-cloak data-lucide="eye-off" class="w-5 h-5"></i>
                    </button>
                </div>
            </div>

            {{-- Password Baru --}}
            <div>
                <label for="password" class="block text-sm font-medium text-slate-700 mb-1">Password Baru</label>
                <div class="relative">
                    <input 
                        :type="showNew ? 'text' : 'password'" 
                        id="password" 
                        name="password" 
                        required 
                        x-model="newPassword" 
                        @input="checkStrength()" 
                        class="w-full px-4 py-2.5 pr-10 rounded-lg border border-slate-300 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 outline-none transition"
                        placeholder="Min. 8 karakter, huruf + angka"
                    >
                    <button type="button" @click="showNew = !showNew" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">
                        <i x-show="!showNew" data-lucide="eye" class="w-5 h-5"></i>
                        <i x-show="showNew" x-cloak data-lucide="eye-off" class="w-5 h-5"></i>
                    </button>
                </div>
                <div class="mt-2 flex items-center gap-2">
                    <div class="flex-1 h-1.5 bg-slate-200 rounded-full overflow-hidden">
                        <div class="h-full transition-all duration-300" :class="strengthColor" :style="`width: ${strengthPercent}%`"></div>
                    </div>
                    <span class="text-xs font-medium text-slate-600" x-text="strengthLabel"></span>
                </div>
                <p class="mt-1 text-xs text-slate-500">Hanya huruf dan angka, tanpa simbol/spasi.</p>
            </div>

            {{-- Konfirmasi Password Baru --}}
            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-slate-700 mb-1">Konfirmasi Password Baru</label>
                <div class="relative">
                    <input 
                        :type="showConfirm ? 'text' : 'password'" 
                        id="password_confirmation" 
                        name="password_confirmation" 
                        required 
                        x-model="confirmPassword" 
                        class="w-full px-4 py-2.5 pr-10 rounded-lg border border-slate-300 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 outline-none transition"
                        :class="confirmPassword && confirmPassword !== newPassword ? 'border-red-300' : (confirmPassword && confirmPassword === newPassword ? 'border-emerald-300' : '')"
                        placeholder="Ulangi password baru"
                    >
                    <button type="button" @click="showConfirm = !showConfirm" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">
                        <i x-show="!showConfirm" data-lucide="eye" class="w-5 h-5"></i>
                        <i x-show="showConfirm" x-cloak data-lucide="eye-off" class="w-5 h-5"></i>
                    </button>
                </div>
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
</div>

<style>[x-cloak] { display: none !important; }</style>

<script>
function passwordForm() {
    return {
        showCurrent: false,
        showNew: false,
        showConfirm: false,
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

// Re-render Lucide icons setelah Alpine load
document.addEventListener('alpine:initialized', () => {
    lucide.createIcons();
});
</script>
@endsection