<?php

namespace App\Modules\Rumah\Services;

use App\Models\AuditLog;
use App\Models\RumahWarga;
use App\Models\Wilayah;
use App\Modules\Core\Services\AuditLogger;
use App\Modules\Core\Services\NikEncryptor;
use Illuminate\Support\Facades\DB;

class RumahService
{
    public function __construct(
        private readonly NikEncryptor $nikEncryptor,
        private readonly AuditLogger $auditLogger,
    ) {
    }

    public function createRumah(array $data): RumahWarga
    {
        return DB::transaction(function () use ($data) {
            $wilayahRt = $this->resolveOrCreateRtWilayah(
                parentWilayahId: (int) $data['wilayah_id'],
                rtText: $data['rt_text'],
                rwText: $data['rw_text'],
            );

            $kode = $this->generateKodeRegistrasi();

            $payload = [
                'kode_registrasi'      => $kode,
                'nama_kepala_keluarga' => trim($data['nama_kepala_keluarga']),
                'nik_enc'              => $this->nikEncryptor->encryptNik($data['nik']),
                'nik_hash'             => $this->nikEncryptor->hashNik($data['nik']),
                'no_kk_enc'            => $this->nikEncryptor->encryptNoKk($data['no_kk']),
                'no_kk_hash'           => $this->nikEncryptor->hashNoKk($data['no_kk']),
                'jumlah_anggota'       => (int) $data['jumlah_anggota'],
                'luas_rumah'           => $data['luas_rumah'] ?? null,
                'pekerjaan'            => $data['pekerjaan'] ?? null,
                'penghasilan'          => $data['penghasilan'] ?? null,
                'status_kepemilikan'   => $data['status_kepemilikan'],
                'alamat'               => trim($data['alamat']),
                'latitude'             => $data['latitude'] ?? null,
                'longitude'            => $data['longitude'] ?? null,
                'wilayah_id'           => $wilayahRt->id,
                'surveyor_id'          => (int) $data['surveyor_id'],
                'status'               => RumahWarga::STATUS_MENUNGGU_DISURVEI,
            ];

            $rumah = RumahWarga::create($payload);

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
        return "RUMAH-{$year}-{$seq}";
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

    public function updateRumah(RumahWarga $rumah, array $data): RumahWarga
    {
        if (!$rumah->bisaDieditOlehAdmin()) {
            throw new \RuntimeException(
                "Data rumah tidak bisa diedit karena sudah dalam proses survei (status: {$rumah->status})."
            );
        }

        $dataLama = $rumah->only(['nama_kepala_keluarga', 'jumlah_anggota', 'penghasilan', 'alamat', 'wilayah_id']);

        DB::transaction(function () use ($rumah, $data) {
            $rumah->forceFill([
                'nama_kepala_keluarga' => trim($data['nama_kepala_keluarga']),
                'jumlah_anggota'       => (int) $data['jumlah_anggota'],
                'luas_rumah'           => $data['luas_rumah'] ?? null,
                'pekerjaan'            => $data['pekerjaan'] ?? null,
                'penghasilan'          => $data['penghasilan'] ?? null,
                'status_kepemilikan'   => $data['status_kepemilikan'],
                'alamat'               => trim($data['alamat']),
                'latitude'             => $data['latitude'] ?? null,
                'longitude'            => $data['longitude'] ?? null,
                'surveyor_id'          => (int) $data['surveyor_id'],
            ])->save();

            if (!empty($data['wilayah_id']) && !empty($data['rw_text']) && !empty($data['rt_text'])) {
                $wilayahRt = $this->resolveOrCreateRtWilayah(
                    parentWilayahId: (int) $data['wilayah_id'],
                    rtText: $data['rt_text'],
                    rwText: $data['rw_text'],
                );
                $rumah->forceFill(['wilayah_id' => $wilayahRt->id])->save();
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
                dataBaru:  $rumah->fresh()->only(['nama_kepala_keluarga', 'penghasilan', 'alamat', 'wilayah_id']),
                dataLama:  $dataLama,
            );
        });

        return $rumah->fresh();
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
