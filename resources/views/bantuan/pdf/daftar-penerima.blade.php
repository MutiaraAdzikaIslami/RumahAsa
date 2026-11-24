<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $judulLaporan ?? 'Daftar Penerima Bantuan' }} — RumahAsa</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1e293b; margin: 20px; }
        .header { border-bottom: 2px solid #1e4631; padding-bottom: 10px; margin-bottom: 16px; }
        .title { font-size: 18px; font-weight: bold; color: #1e4631; margin: 0; }
        .subtitle { font-size: 11px; color: #64748b; margin-top: 4px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        th { background-color: #1e4631; color: #ffffff; text-align: left; padding: 8px 10px; font-size: 10px; text-transform: uppercase; }
        td { padding: 7px 10px; border-bottom: 1px solid #e2e8f0; font-size: 10.5px; }
        tr:nth-child(even) td { background-color: #f8fafc; }
        .footer { margin-top: 20px; font-size: 9.5px; color: #64748b; border-top: 1px solid #e2e8f0; padding-top: 8px; }
    </style>
</head>
<body>
    <div class="header">
        <p class="title">RumahAsa — {{ $judulLaporan ?? 'Laporan Daftar Penerima Bantuan & Progres Renovasi' }}</p>
        <p class="subtitle">Dicetak pada {{ now()->translatedFormat('d F Y, H:i') }} WIB &middot; Oleh {{ auth()->user()?->nama }} ({{ auth()->user()?->role?->nama }})</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Kode</th>
                <th>Nama Pemilik</th>
                <th>NIK (Tersamar)</th>
                <th>Alamat</th>
                <th>Wilayah</th>
                <th>Status</th>
                <th>Tahap</th>
                <th>Progres</th>
                <th>Terakhir Diperbarui</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($daftarRumah as $idx => $r)
                @php
                    $bantuan = $r->penerimaBantuan;
                    $persen = $bantuan ? $bantuan->progresTerakhir() : 0;
                    $progresTerbaru = $bantuan?->progres?->sortByDesc('tanggal_progres')->first();
                @endphp
                <tr>
                    <td>{{ $idx + 1 }}</td>
                    <td>{{ $r->kode_registrasi }}</td>
                    <td><strong>{{ $r->nama_kepala_keluarga }}</strong></td>
                    <td>{{ $r->nik_enc ? $r->nikTersamar() : '-' }}</td>
                    <td>{{ $r->alamatLengkap() }}</td>
                    <td>{{ $r->wilayahSingkat() }}</td>
                    <td>{{ $r->status }}</td>
                    <td>{{ $progresTerbaru?->tahap_pekerjaan ?? ($r->status === 'Selesai' ? 'Finishing' : 'Persiapan') }}</td>
                    <td>{{ $persen }}%</td>
                    <td>{{ $r->terakhirDiperbaruiLabel() }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" style="text-align: center;">Tidak ada data penerima bantuan pada filter ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Dokumen resmi RumahAsa &middot; Sesuai UU No. 27 Tahun 2022 tentang Pelindungan Data Pribadi (UU PDP), NIK warga ditampilkan dalam format tersamar dan aktivitas ekspor ini tercatat di dalam Audit Log sistem.
    </div>
</body>
</html>
