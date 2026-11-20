<?php

namespace App\Modules\Rumah\Services;

use App\Models\AuditLog;
use App\Models\RumahWarga;
use App\Models\User;
use App\Models\Wilayah;
use App\Modules\Core\Services\AuditLogger;
use App\Modules\Core\Services\NikEncryptor;
use App\Modules\Workflow\Services\StatusWorkflow;
use Illuminate\Support\Facades\DB;

class RumahService
{
    public function __construct(
        private readonly NikEncryptor $nikEncryptor,
        private readonly AuditLogger $auditLogger,
        private readonly StatusWorkflow $statusWorkflow,
    ) {
    }

    public function createRumah(array $data, bool $isDraft = false): RumahWarga
    {
        // Validasi Surveyor dan Wilayah Tugas jika keduanya diisi
        if (!empty($data['surveyor_id']) && !empty($data['wilayah_id'])) {
            $surveyor = User::with(['role', 'wilayah'])->find($data['surveyor_id']);
            $kelurahan = Wilayah::find($data['wilayah_id']);
            if ($surveyor && $kelurahan) {
                $this->validateSurveyorWilayah($surveyor, $kelurahan);
            }
        }

        return DB::transaction(function () use ($data, $isDraft) {
            $wilayahId = null;
            if (!empty($data['wilayah_id'])) {
                if (!empty($data['rt_text']) && !empty($data['rw_text'])) {
                    $wilayahRt = $this->resolveOrCreateRtWilayah(
                        parentWilayahId: (int) $data['wilayah_id'],
                        rtText: $data['rt_text'],
                        rwText: $data['rw_text'],
                    );
                    $wilayahId = $wilayahRt->id;
                } else {
                    $wilayahId = (int) $data['wilayah_id'];
                }
            }

            $kode = $this->generateKodeRegistrasi();

            $payload = [
                'kode_registrasi'      => $kode,
                'nama_kepala_keluarga' => isset($data['nama_kepala_keluarga']) && $data['nama_kepala_keluarga'] !== ''
                    ? trim((string) $data['nama_kepala_keluarga'])
                    : null,
                'nik_enc'              => !empty($data['nik']) ? $this->nikEncryptor->encryptNik($data['nik']) : null,
                'nik_hash'             => !empty($data['nik']) ? $this->nikEncryptor->hashNik($data['nik']) : null,
                'no_kk_enc'            => !empty($data['no_kk']) ? $this->nikEncryptor->encryptNoKk($data['no_kk']) : null,
                'no_kk_hash'           => !empty($data['no_kk']) ? $this->nikEncryptor->hashNoKk($data['no_kk']) : null,
                'jumlah_anggota'       => isset($data['jumlah_anggota']) && $data['jumlah_anggota'] !== '' ? (int) $data['jumlah_anggota'] : null,
                'luas_rumah'           => $data['luas_rumah'] ?? null,
                'pekerjaan'            => $data['pekerjaan'] ?? null,
                'penghasilan'          => $data['penghasilan'] ?? null,
                'status_kepemilikan'   => $data['status_kepemilikan'] ?? null,
                'alamat'               => isset($data['alamat']) && $data['alamat'] !== '' ? trim((string) $data['alamat']) : null,
                'wilayah_id'           => $wilayahId,
                'surveyor_id'          => (int) $data['surveyor_id'],
            ];

            $rumah = RumahWarga::create($payload)->fresh();

            if (!$isDraft) {
                $this->statusWorkflow->daftarkan($rumah);
            }

            $this->auditLogger->catat(
                aksi:      AuditLog::AKSI['RUMAH_CREATE'],
                entitas:   'rumah_warga',
                entitasId: $rumah->id,
                dataBaru:  [
                    'kode_registrasi'      => $rumah->kode_registrasi,
                    'nama_kepala_keluarga' => $rumah->nama_kepala_keluarga,
                    'wilayah_id'           => $rumah->wilayah_id,
                    'surveyor_id'          => $rumah->surveyor_id,
                    'status'               => $rumah->status,
                ],
            );

            return $rumah;
        });
    }

    public function generateKodeRegistrasi(): string
    {
        $year  = now()->year;
        $count = RumahWarga::withTrashed()->whereYear('created_at', $year)->count();
        $seq   = str_pad($count + 1, 4, '0', STR_PAD_LEFT);
        return "RW-{$year}-{$seq}";
    }

    public function resolveOrCreateRtWilayah(int $parentWilayahId, string $rtText, string $rwText): Wilayah
    {
        $parent = Wilayah::findOrFail($parentWilayahId);

        if ($parent->tingkat !== Wilayah::TINGKAT_KELURAHAN) {
            throw new \InvalidArgumentException(
                'Wilayah yang dipilih harus berupa Kelurahan/Desa (bukan kabupaten/kecamatan).'
            );
        }

        $rwText = trim($rwText);
        $rtText = trim($rtText);

        $rwNama = 'RW ' . mb_strtoupper($rwText);
        $rw = Wilayah::firstOrCreate(
            ['parent_id' => $parent->id, 'nama' => $rwNama],
            [
                'kode'     => 'RW-' . $parent->id . '-' . mb_strtoupper($rwText),
                'tingkat'  => Wilayah::TINGKAT_RW,
                'path'     => null,
                'is_aktif' => true,
            ],
        );
        if (blank($rw->path)) {
            $rw->forceFill(['path' => $parent->path . '/' . $rw->id])->save();
        }

        $rtNama = 'RT ' . mb_strtoupper($rtText);
        $rt = Wilayah::firstOrCreate(
            ['parent_id' => $rw->id, 'nama' => $rtNama],
            [
                'kode'     => 'RT-' . $rw->id . '-' . mb_strtoupper($rtText),
                'tingkat'  => Wilayah::TINGKAT_RT,
                'path'     => null,
                'is_aktif' => true,
            ],
        );
        if (blank($rt->path)) {
            $rt->forceFill(['path' => $rw->path . '/' . $rt->id])->save();
        }

        return $rt->fresh();
    }

    public function updateRumah(RumahWarga $rumah, array $data, bool $isDraft = false): RumahWarga
    {
        if (!$rumah->bisaDieditOlehAdmin()) {
            throw new \RuntimeException(
                "Data rumah tidak bisa diedit karena sudah dalam proses survei (status: {$rumah->status})."
            );
        }

        // Validasi Surveyor dan Wilayah Tugas jika surveyor diubah/diupdate
        if (!empty($data['surveyor_id'])) {
            $surveyor = User::with(['role', 'wilayah'])->find($data['surveyor_id']);
            $targetWilayahId = !empty($data['wilayah_id']) ? (int) $data['wilayah_id'] : $rumah->wilayah_id;
            $targetWilayah = $targetWilayahId ? Wilayah::find($targetWilayahId) : null;
            if ($surveyor && $targetWilayah) {
                $this->validateSurveyorWilayah($surveyor, $targetWilayah);
            }
        }

        $dataLama = $rumah->only(['nama_kepala_keluarga', 'jumlah_anggota', 'penghasilan', 'alamat', 'wilayah_id', 'status']);

        DB::transaction(function () use ($rumah, $data, $isDraft, $dataLama) {

            $rumah->forceFill([
                'nama_kepala_keluarga' => isset($data['nama_kepala_keluarga']) && $data['nama_kepala_keluarga'] !== ''
                    ? trim((string) $data['nama_kepala_keluarga'])
                    : null,
                'jumlah_anggota'       => isset($data['jumlah_anggota']) && $data['jumlah_anggota'] !== '' ? (int) $data['jumlah_anggota'] : null,
                'luas_rumah'           => $data['luas_rumah'] ?? null,
                'pekerjaan'            => $data['pekerjaan'] ?? null,
                'penghasilan'          => $data['penghasilan'] ?? null,
                'status_kepemilikan'   => $data['status_kepemilikan'] ?? null,
                'alamat'               => isset($data['alamat']) && $data['alamat'] !== '' ? trim((string) $data['alamat']) : null,
                'surveyor_id'          => (int) $data['surveyor_id'],
            ])->save();

            if (!$isDraft && $rumah->status === RumahWarga::STATUS_DRAF) {
                $this->statusWorkflow->daftarkan($rumah);
            }

            if (!empty($data['wilayah_id'])) {
                if (!empty($data['rw_text']) && !empty($data['rt_text'])) {
                    $wilayahRt = $this->resolveOrCreateRtWilayah(
                        parentWilayahId: (int) $data['wilayah_id'],
                        rtText: $data['rt_text'],
                        rwText: $data['rw_text'],
                    );
                    $rumah->forceFill(['wilayah_id' => $wilayahRt->id])->save();
                } else {
                    $rumah->forceFill(['wilayah_id' => (int) $data['wilayah_id']])->save();
                }
            }

            if (!empty($data['nik'])) {
                $rumah->forceFill([
                    'nik_enc'  => $this->nikEncryptor->encryptNik($data['nik']),
                    'nik_hash' => $this->nikEncryptor->hashNik($data['nik']),
                ])->save();
            }
            if (!empty($data['no_kk'])) {
                $rumah->forceFill([
                    'no_kk_enc'  => $this->nikEncryptor->encryptNoKk($data['no_kk']),
                    'no_kk_hash' => $this->nikEncryptor->hashNoKk($data['no_kk']),
                ])->save();
            }

            $this->auditLogger->catat(
                aksi:      AuditLog::AKSI['RUMAH_UPDATE'],
                entitas:   'rumah_warga',
                entitasId: $rumah->id,
                dataBaru:  $rumah->fresh()->only(['nama_kepala_keluarga', 'penghasilan', 'alamat', 'wilayah_id', 'status']),
                dataLama:  $dataLama,
            );
        });

        return $rumah->fresh();
    }

    /**
     * Memvalidasi apakah wilayah tugas surveyor mencakup wilayah rumah yang ditugaskan.
     *
     * @throws \InvalidArgumentException
     */
    public function validateSurveyorWilayah(User $surveyor, Wilayah $wilayah): void
    {
        if ($surveyor->role?->kode !== 'surveyor') {
            throw new \InvalidArgumentException("User {$surveyor->nama} bukan seorang surveyor.");
        }

        if (!$surveyor->is_aktif) {
            throw new \InvalidArgumentException("Surveyor {$surveyor->nama} sedang berstatus non-aktif.");
        }

        $wilayahSurveyor = $surveyor->wilayah;
        if (!$wilayahSurveyor) {
            throw new \InvalidArgumentException(
                "Surveyor {$surveyor->nama} belum memiliki wilayah tugas. Silakan atur wilayah tugas terlebih dahulu di Manajemen User."
            );
        }

        $isMatch = false;

        // 1. Jika ID wilayah sama persis
        if ($wilayah->id === $wilayahSurveyor->id) {
            $isMatch = true;
        }
        // 2. Jika path wilayah rumah diawali oleh path wilayah surveyor (hierarki ke bawah)
        elseif (!empty($wilayah->path) && !empty($wilayahSurveyor->path) && str_starts_with($wilayah->path, rtrim($wilayahSurveyor->path, '/') . '/')) {
            $isMatch = true;
        }
        // 3. Fallback jika path belum digenerate: cek parent_id
        elseif ($wilayah->parent_id === $wilayahSurveyor->id) {
            $isMatch = true;
        }
        // 4. Lingkup KECAMATAN: izinkan jika surveyor dan rumah berada di dalam satu kecamatan yang sama
        else {
            $kecamatanSurveyor = $this->resolveKecamatan($wilayahSurveyor);
            $kecamatanRumah    = $this->resolveKecamatan($wilayah);

            if ($kecamatanSurveyor && $kecamatanRumah && $kecamatanSurveyor->id === $kecamatanRumah->id) {
                $isMatch = true;
            }
        }

        if (!$isMatch) {
            $kecamatanSurveyor = $this->resolveKecamatan($wilayahSurveyor);
            $kecamatanRumah    = $this->resolveKecamatan($wilayah);
            $namaKecSurveyor   = $kecamatanSurveyor ? "Kecamatan {$kecamatanSurveyor->nama}" : "{$wilayahSurveyor->nama} (" . ucfirst($wilayahSurveyor->tingkat) . ')';
            $namaKecRumah      = $kecamatanRumah ? "Kecamatan {$kecamatanRumah->nama}" : "{$wilayah->nama} (" . ucfirst($wilayah->tingkat) . ')';

            throw new \InvalidArgumentException(
                "Surveyor {$surveyor->nama} bertugas di {$namaKecSurveyor}, berbeda dengan wilayah rumah di {$namaKecRumah} ({$wilayah->nama}). Harap pilih surveyor yang bertugas di {$namaKecRumah}."
            );
        }
    }

    /**
     * Mengambil model Kecamatan dari suatu hierarki wilayah.
     */
    public function resolveKecamatan(Wilayah $wilayah): ?Wilayah
    {
        if ($wilayah->tingkat === Wilayah::TINGKAT_KECAMATAN) {
            return $wilayah;
        }

        if (!empty($wilayah->path)) {
            $segments = array_values(array_filter(explode('/', $wilayah->path)));
            // Format path: /provinsi/kabupaten/kecamatan/... (indeks 2 adalah id kecamatan)
            if (count($segments) >= 3) {
                return Wilayah::find((int) $segments[2]);
            }
        }

        $current = $wilayah;
        while ($current) {
            if ($current->tingkat === Wilayah::TINGKAT_KECAMATAN) {
                return $current;
            }
            $current = $current->parent;
        }

        return null;
    }

    public function deleteRumah(RumahWarga $rumah): void
    {
        if (!$rumah->bisaDieditOlehAdmin()) {
            throw new \RuntimeException('Rumah tidak bisa dihapus karena sudah dalam proses survei.');
        }

        DB::transaction(function () use ($rumah) {
            $rumah->delete();
            $this->auditLogger->catat(
                aksi:      AuditLog::AKSI['RUMAH_DELETE'],
                entitas:   'rumah_warga',
                entitasId: $rumah->id,
                dataLama:  ['kode_registrasi' => $rumah->kode_registrasi, 'nama' => $rumah->nama_kepala_keluarga],
            );
        });
    }
}