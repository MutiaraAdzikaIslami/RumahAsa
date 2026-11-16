@php
    $flash = function ($type) {
        $map = [
            'success' => 'bg-forest-50 border-forest-300 text-forest-800',
            'error'   => 'bg-red-50 border-red-300 text-red-800',
            'warning' => 'bg-amber-50 border-amber-300 text-amber-800',
            'info'    => 'bg-cream-100 border-forest-200 text-forest-800',
        ];
        return $map[$type] ?? $map['info'];
    };
@endphp

@foreach (['success', 'error', 'warning', 'info'] as $type)
    @if (session($type))
        <div class="mb-4 rounded-md border px-4 py-3 text-sm {{ $flash($type) }}" role="alert">
            {{ session($type) }}
        </div>
    @endif
@endforeach

@if (session('generated_password'))
    <div class="mb-4 overflow-hidden rounded-lg border-2 border-clay-400 bg-clay-400/10 shadow-sm" role="alert" aria-live="polite">
        <div class="border-b border-clay-400/40 bg-clay-400/15 px-4 py-2.5">
            <p class="text-sm font-bold text-clay-600">Password sementara untuk user: {{ session('generated_username') }}</p>
            <p class="mt-0.5 text-xs text-clay-600/80">Salin sekarang. Password ini tidak akan ditampilkan lagi.</p>
        </div>
        <div class="flex items-center justify-between gap-3 px-4 py-3">
            <code class="select-all rounded bg-white px-3 py-2 font-mono text-lg font-bold tracking-wider text-forest-900 shadow-inner">{{ session('generated_password') }}</code>
            <button type="button"
                    onclick="navigator.clipboard?.writeText('{{ e(session('generated_password')) }}'); this.textContent='Tersalin';"
                    class="shrink-0 rounded-md bg-forest-600 px-3 py-1.5 text-sm font-medium text-white transition hover:bg-forest-700">
                Salin
            </button>
        </div>
    </div>
@endif
