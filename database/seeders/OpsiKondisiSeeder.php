<?php

namespace Database\Seeders;

use App\Models\KomponenPenilaian;
use App\Models\OpsiKondisi;
use Illuminate\Database\Seeder;

class OpsiKondisiSeeder extends Seeder
{
    public function run(): void
    {
        $list = self::listOpsi();
        foreach ($list as $o) {
            $komponen = KomponenPenilaian::where('kode', $o['komponen_kode'])->first();
            if (!$komponen) continue;
            unset($o['komponen_kode']);
            $o['komponen_id'] = $komponen->id;
            OpsiKondisi::updateOrCreate(
                ['komponen_id' => $komponen->id, 'kode' => $o['kode']],
                $o
            );
        }
    }

    /**
     * Definisi 33 opsi kondisi per komponen (sesuai panduan RTLH pemerintah)
     * Field "otomatis_rtlh" = true untuk opsi yang secara panduan otomatis menjadikan rumah RTLH.
     * CATATAN: sistem TIDAK otomatis menetapkan kategori; ini hanya panduan untuk Verifikator.
     */
    public static function listOpsi(): array
    {
        $otomatis = true;  // shortcut biar singkat

        return [
            // === 1. Struktur Bangunan ===
            ['komponen_kode' => 'struktur', 'urutan' => 1, 'kode' => 'baik', 'label' => 'Baik', 'deskripsi' => 'Tidak ada kerusakan fisik, tidak ada penurunan fondasi, kemiringan ≤5°, dimensi kolom memenuhi standar (≥10×10 cm dengan besi min. 8 mm untuk beton).', 'otomatis_rtlh' => false],
            ['komponen_kode' => 'struktur', 'urutan' => 2, 'kode' => 'rusak_ringan', 'label' => 'Rusak Ringan', 'deskripsi' => 'Kerusakan fisik <30% jumlah tiang, kemiringan ≤5°, tidak ada penurunan fondasi signifikan.', 'otomatis_rtlh' => false],
            ['komponen_kode' => 'struktur', 'urutan' => 3, 'kode' => 'rusak_berat_fondasi', 'label' => 'Rusak Berat - Fondasi', 'deskripsi' => 'Penurunan fondasi lokal/merata yang menyebabkan lantai/dinding retak signifikan, ATAU kerusakan fisik fondasi >30%.', 'otomatis_rtlh' => $otomatis],
            ['komponen_kode' => 'struktur', 'urutan' => 4, 'kode' => 'rusak_berat_kolom', 'label' => 'Rusak Berat - Kolom/Balok', 'deskripsi' => 'Kerusakan fisik (patah/keropos/lapuk) >30% jumlah tiang, ATAU kemiringan struktur >5° dari garis tegak lurus.', 'otomatis_rtlh' => $otomatis],
            ['komponen_kode' => 'struktur', 'urutan' => 5, 'kode' => 'kritis_dimensi', 'label' => 'Kritis - Dimensi Tidak Memenuhi', 'deskripsi' => 'Kolom beton berdimensi <10×10 cm atau tanpa tulangan besi. Otomatis dihitung tidak layak.', 'otomatis_rtlh' => $otomatis],

            // === 2. Atap ===
            ['komponen_kode' => 'atap', 'urutan' => 1, 'kode' => 'baik', 'label' => 'Baik', 'deskripsi' => 'Tidak bocor, material layak (genteng, seng baik, dll).', 'otomatis_rtlh' => false],
            ['komponen_kode' => 'atap', 'urutan' => 2, 'kode' => 'rusak_ringan', 'label' => 'Rusak Ringan', 'deskripsi' => 'Bocor/patah/keropos <30% luas atap.', 'otomatis_rtlh' => false],
            ['komponen_kode' => 'atap', 'urutan' => 3, 'kode' => 'rusak_berat', 'label' => 'Rusak Berat', 'deskripsi' => 'Bocor/patah/keropos >30% luas atap.', 'otomatis_rtlh' => true],
            ['komponen_kode' => 'atap', 'urutan' => 4, 'kode' => 'material_tidak_layak', 'label' => 'Material Tidak Layak', 'deskripsi' => 'Penutup atap berupa daun/rumbia/plastik/terpal, ATAU seng berkarat/bolong >30%. Otomatis RTLH.', 'otomatis_rtlh' => $otomatis],

            // === 3. Dinding ===
            ['komponen_kode' => 'dinding', 'urutan' => 1, 'kode' => 'baik', 'label' => 'Baik', 'deskripsi' => 'Tidak ada kerusakan, material layak (tembok plester, dll).', 'otomatis_rtlh' => false],
            ['komponen_kode' => 'dinding', 'urutan' => 2, 'kode' => 'rusak_ringan', 'label' => 'Rusak Ringan', 'deskripsi' => 'Retak non-struktural <30% luas dinding.', 'otomatis_rtlh' => false],
            ['komponen_kode' => 'dinding', 'urutan' => 3, 'kode' => 'rusak_berat', 'label' => 'Rusak Berat', 'deskripsi' => 'Bolong/retak tembus depan-belakang/rapuh dengan akumulasi luas >30% total luas dinding.', 'otomatis_rtlh' => true],
            ['komponen_kode' => 'dinding', 'urutan' => 4, 'kode' => 'material_tidak_layak', 'label' => 'Material Tidak Layak', 'deskripsi' => 'Bambu anyaman kasar (gedek) tanpa plester, papan kayu/tripleks tidak rapat, terpal/kardus/plastik. Otomatis 100% RTLH.', 'otomatis_rtlh' => $otomatis],

            // === 4. Lantai ===
            ['komponen_kode' => 'lantai', 'urutan' => 1, 'kode' => 'baik', 'label' => 'Baik', 'deskripsi' => 'Semen/ubin utuh, atau kayu panggung layak (tidak lapuk).', 'otomatis_rtlh' => false],
            ['komponen_kode' => 'lantai', 'urutan' => 2, 'kode' => 'semen_pecah', 'label' => 'Rusak - Plesteran Pecah', 'deskripsi' => 'Plesteran semen pecah/terkelupas hingga terlihat tanah, luas >30%.', 'otomatis_rtlh' => true],
            ['komponen_kode' => 'lantai', 'urutan' => 3, 'kode' => 'kayu_lapuk', 'label' => 'Kayu/Bambu Lapuk', 'deskripsi' => 'Lantai kayu/bambu di atas panggung, tetapi sudah lapuk >30%.', 'otomatis_rtlh' => true],
            ['komponen_kode' => 'lantai', 'urutan' => 4, 'kode' => 'tanah', 'label' => 'Tanah', 'deskripsi' => 'Lantai tanah langsung. Otomatis 100% RTLH (skor terendah).', 'otomatis_rtlh' => $otomatis],
            ['komponen_kode' => 'lantai', 'urutan' => 5, 'kode' => 'kayu_tanpa_panggung', 'label' => 'Kayu/Bambu Tanpa Panggung', 'deskripsi' => 'Lantai kayu/bambu diletakkan langsung di atas tanah tanpa panggung. Otomatis RTLH.', 'otomatis_rtlh' => $otomatis],

            // === 5. Kecukupan Luas Ruang ===
            ['komponen_kode' => 'luas_ruang', 'urutan' => 1, 'kode' => 'memadai', 'label' => 'Memadai', 'deskripsi' => 'Luas ≥9 m² per orang (standar SDG) DAN tinggi plafon ≥2,8 m.', 'otomatis_rtlh' => false],
            ['komponen_kode' => 'luas_ruang', 'urutan' => 2, 'kode' => 'kurang_luas', 'label' => 'Kurang Luas', 'deskripsi' => 'Luas <9 m² per orang (di bawah standar SDG), atau <7,2 m² per orang (batas bawah BPS).', 'otomatis_rtlh' => false],
            ['komponen_kode' => 'luas_ruang', 'urutan' => 3, 'kode' => 'plafon_rendah', 'label' => 'Plafon Rendah', 'deskripsi' => 'Tinggi ruangan (lantai ke batas bawah plafon/atap) <2,8 m. Sirkulasi udara dinilai buruk.', 'otomatis_rtlh' => false],
            ['komponen_kode' => 'luas_ruang', 'urutan' => 4, 'kode' => 'tidak_memadai', 'label' => 'Tidak Memadai', 'deskripsi' => 'Kurang luas DAN plafon rendah.', 'otomatis_rtlh' => true],

            // === 6. Pencahayaan & Penghawaan ===
            ['komponen_kode' => 'pencahayaan', 'urutan' => 1, 'kode' => 'memadai', 'label' => 'Memadai', 'deskripsi' => 'Ventilasi (lubang sirkulasi udara yang bisa dibuka) ≥5% luas lantai DAN pencahayaan alami (jendela kaca) ≥10% luas lantai.', 'otomatis_rtlh' => false],
            ['komponen_kode' => 'pencahayaan', 'urutan' => 2, 'kode' => 'ventilasi_kurang', 'label' => 'Ventilasi Kurang', 'deskripsi' => 'Luas lubang sirkulasi udara yang bisa dibuka <5% luas lantai.', 'otomatis_rtlh' => false],
            ['komponen_kode' => 'pencahayaan', 'urutan' => 3, 'kode' => 'pencahayaan_kurang', 'label' => 'Pencahayaan Kurang', 'deskripsi' => 'Luas jendela kaca atau genteng kaca <10% luas lantai.', 'otomatis_rtlh' => false],
            ['komponen_kode' => 'pencahayaan', 'urutan' => 4, 'kode' => 'tidak_memadai', 'label' => 'Tidak Memadai', 'deskripsi' => 'Ventilasi <5% DAN pencahayaan <10%. Otomatis tidak layak sehat.', 'otomatis_rtlh' => true],

            // === 7. Fasilitas Sanitasi ===
            ['komponen_kode' => 'sanitasi', 'urutan' => 1, 'kode' => 'layak', 'label' => 'Layak', 'deskripsi' => 'Jarak sumur-septic tank ≥10 m (tanah padat) atau ≥15 m (tanah porus), kloset bertipe leher angsa.', 'otomatis_rtlh' => false],
            ['komponen_kode' => 'sanitasi', 'urutan' => 2, 'kode' => 'jarak_sumur_tidak_aman', 'label' => 'Jarak Sumur Tidak Aman', 'deskripsi' => 'Jarak sumur air minum ke septic tank <10 m (atau <15 m di tanah porus). Risiko tercemar E.coli.', 'otomatis_rtlh' => true],
            ['komponen_kode' => 'sanitasi', 'urutan' => 3, 'kode' => 'kloset_tidak_layak', 'label' => 'Kloset Tidak Layak', 'deskripsi' => 'Menggunakan kloset cemplung terbuka tanpa leher angsa.', 'otomatis_rtlh' => true],
            ['komponen_kode' => 'sanitasi', 'urutan' => 4, 'kode' => 'tidak_ada_sanitasi', 'label' => 'Tidak Ada Sanitasi', 'deskripsi' => 'Tidak ada kloset/MCK di rumah.', 'otomatis_rtlh' => true],
        ];
    }
}
