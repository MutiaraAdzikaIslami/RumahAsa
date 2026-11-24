<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekap Dana Bantuan — RumahAsa</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1e293b; margin: 20px; }
        .header { border-bottom: 2px solid #1e4631; padding-bottom: 10px; margin-bottom: 16px; }
        .title { font-size: 18px; font-weight: bold; color: #1e4631; margin: 0; }
        .subtitle { font-size: 11px; color: #64748b; margin-top: 4px; }
        .meta-box { background: #f8fafc; border: 1px solid #e2e8f0; padding: 10px 14px; border-radius: 6px; margin-bottom: 16px; }
        .meta-box span { display: inline-block; margin-right: 24px; }
        h3 { font-size: 13px; color: #0f172a; margin: 14px 0 8px 0; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        th { background-color: #1e4631; color: #ffffff; text-align: left; padding: 8px 10px; font-size: 10px; text-transform: uppercase; }
        td { padding: 7px 10px; border-bottom: 1px solid #e2e8f0; font-size: 10.5px; }
        tr:nth-child(even) td { background-color: #f8fafc; }
        .total-row td { background-color: #ecfdf5; font-weight: bold; color: #1e4631; border-top: 2px solid #1e4631; }
        .footer { margin-top: 20px; font-size: 9.5px; color: #64748b; border-top: 1px solid #e2e8f0; padding-top: 8px; }
    </style>
</head>
<body>
    <div class="header">
        <p class="title">RumahAsa — Laporan Rekap Dana Bantuan Renovasi</p>
        <p class="subtitle">Dicetak pada {{ now()->translatedFormat('d F Y, H:i') }} WIB &middot; Oleh {{ auth()->user()?->nama }} ({{ auth()->user()?->role?->nama }})</p>
    </div>

    <div class="meta-box">
        <span><strong>Tahun:</strong> {{ $tahun }}</span>
        <span><strong>Triwulan:</strong> {{ $triwulan ? 'Triwulan ' . $triwulan : 'Semua Triwulan' }}</span>
        <span><strong>Wilayah:</strong> {{ $namaWilayah ?? 'Semua Wilayah' }}</span>
        <span><strong>Sumber Program:</strong> {{ $sumberProgram ?: 'Semua Sumber Program' }}</span>
    </div>

    <h3>1. Ringkasan per Sumber Program</h3>
    <table>
        <thead>
            <tr>
                <th>Sumber Program</th>
                <th>Total Dana Tersalur</th>
                <th>Jumlah Rumah</th>
                <th>Rata-rata per Rumah</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rekapSumber as $sumber => $data)
                <tr>
                    <td><strong>{{ $sumber }}</strong></td>
                    <td>Rp {{ number_format($data['nominal'], 0, ',', '.') }}</td>
                    <td>{{ $data['jumlah'] }} rumah</td>
                    <td>Rp {{ number_format($data['rata_rata'], 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="text-align: center;">Tidak ada data pada filter ini.</td>
                </tr>
            @endforelse
            <tr class="total-row">
                <td>Total Keseluruhan</td>
                <td>Rp {{ number_format($totalNominal, 0, ',', '.') }}</td>
                <td>{{ $totalPenerima }} rumah</td>
                <td>Rp {{ $totalPenerima > 0 ? number_format($totalNominal / $totalPenerima, 0, ',', '.') : 0 }}</td>
            </tr>
        </tbody>
    </table>

    <h3>2. Daftar Rumah Penerima Dana</h3>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Kode</th>
                <th>Nama Kepala Keluarga</th>
                <th>NIK (Tersamar)</th>
                <th>Wilayah</th>
                <th>Jenis Bantuan</th>
                <th>Sumber</th>
                <th>Status</th>
                <th>Nominal</th>
                <th>Tgl Penyaluran</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($daftarBantuan as $idx => $b)
                @php $rm = $b->rumah; @endphp
                <tr>
                    <td>{{ $idx + 1 }}</td>
                    <td>{{ $rm?->kode_registrasi ?? '-' }}</td>
                    <td><strong>{{ $rm?->nama_kepala_keluarga ?? '-' }}</strong></td>
                    <td>{{ $rm && $rm->nik_enc ? $rm->nikTersamar() : '-' }}</td>
                    <td>{{ $rm ? $rm->wilayahSingkat() : '-' }}</td>
                    <td>{{ $b->jenis_bantuan }}</td>
                    <td>{{ $b->sumber_program ?: 'APBD' }}</td>
                    <td>{{ $rm?->status ?? '-' }}</td>
                    <td>Rp {{ number_format($b->nominal, 0, ',', '.') }}</td>
                    <td>{{ $b->tanggal_bantuan ? \Carbon\Carbon::parse($b->tanggal_bantuan)->format('d M Y') : '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" style="text-align: center;">Tidak ada data rumah penerima dana.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Dokumen resmi RumahAsa &middot; Sesuai UU No. 27 Tahun 2022 tentang Pelindungan Data Pribadi (UU PDP), NIK warga ditampilkan dalam format tersamar dan aktivitas ekspor ini tercatat di dalam Audit Log sistem.
    </div>
</body>
</html>
