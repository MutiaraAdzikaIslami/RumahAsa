# RumahAsa — Sistem Pendataan Digital Rumah Tidak Layak Huni (RTLH)

RumahAsa adalah aplikasi digital berbasis web untuk mendata, menilai kelayakan, menetapkan penerima bantuan, dan memantau renovasi Rumah Tidak Layak Huni (RTLH), sebagai pendukung **SDG 11 (Kota dan Permukiman yang Berkelanjutan)**. Dikembangkan oleh **Kelompok 5**, Program Studi S1 Sistem Informasi - Kampus Surabaya, Fakultas Rekayasa Industri, Universitas Telkom, untuk mata kuliah **BBK3EAB3 - Proyek Perangkat Lunak** (Dosen Pengampu: Dr. Berlian Rahmy Lidiawaty, S.ST., M.MT.).

Aplikasi ini dikembangkan dengan metode Scrum dengan alat bantu pemantauan hasil pekerjaan menggunakan Jira board "RumahAsa - SCRUM" (Sprint 0: Minggu 1–8; Sprint 1: Minggu 9–15).

> **Catatan penomoran:** Fase pengerjaan disebut berurutan ("Scrum pertama, kedua, …"), sedangkan nomor tiket Jira bisa tidak berurutan. Dokumen ini selalu menyebut keduanya, misalnya *Scrum kedua / SCRUM-7*.

---

## Daftar Isi

1. [Teknologi yang Digunakan](#teknologi-yang-digunakan)
2. [Fitur Aplikasi](#fitur-aplikasi)
3. [Prasyarat](#prasyarat)
4. [Instalasi & Setup](#instalasi--setup)
   - [Skenario macOS](#skenario-macos-herd--homebrew)
   - [Skenario Windows](#skenario-windows-laragon--xampp)
   - [Skenario Linux (opsional)](#skenario-linux-opsional)
5. [Seeding Data Wilayah](#seeding-data-wilayah)
6. [Konfigurasi .env](#konfigurasi-env)
7. [Struktur Direktori Proyek](#struktur-direktori-proyek-rancangan-target)
8. [Struktur Database](#struktur-database-rancangan-target)
9. [Alur Status Rumah](#alur-status-rumah)
10. [Glosarium](#glosarium)
11. [Panduan Penggunaan per Role](#panduan-penggunaan-per-role)
12. [Aturan Branching & Alur Kerja Git](#aturan-branching--alur-kerja-git)
13. [Konvensi Pesan Commit](#konvensi-pesan-commit)
14. [Pull Request & Review](#pull-request--review)
15. [Definition of Done](#definition-of-done)
16. [Standar Kode & Pengujian](#standar-kode--pengujian)
17. [Aturan Migrasi, Seeder, dan Rute](#aturan-migrasi-seeder-dan-rute)
18. [Keamanan & Data Pribadi](#keamanan--data-pribadi)
19. [Mengatasi Error IDE](#mengatasi-error-ide)
20. [Troubleshooting](#troubleshooting)
21. [Perintah yang Sering Digunakan](#perintah-yang-sering-digunakan)
22. [Lisensi](#lisensi)

---

## Teknologi yang Digunakan

| Teknologi | Versi | Peran |
|---|---|---|
| Laravel | 13.x (saat ini 13.37.0) | Framework backend (arsitektur monolit modular berlapis / MVC) |
| PHP | 8.3 atau lebih baru | Bahasa pemrograman server |
| PostgreSQL | 16 atau lebih baru (anggota tim memakai versi berbeda, mis. 18) | Basis data utama |
| Blade | Bawaan Laravel | Templating tampilan |
| Tailwind CSS | 4.3.3 | Styling antarmuka |
| Alpine.js | 3.17.5 | Interaksi ringan di sisi klien |
| Vite + npm | 8.0.0 | Build dan dev server aset frontend |
| Composer | 2.x | Manajer paket PHP |
| Leaflet | [ISI: setelah dipasang] | Peta interaktif dan heatmap renovasi (FR-31) |
| Plugin heatmap Leaflet | [ISI: nama dan versi setelah dipasang] | Lapisan kepadatan rumah per status |
| OpenStreetMap | – | Peta dasar (tanpa kunci API) |
| Laravel Excel | [ISI: setelah dipasang] | Ekspor laporan Excel |
| DomPDF | [ISI: setelah dipasang] | Ekspor laporan PDF |
| PHPUnit / Pest | PHPUnit 12.5.40 | Pengujian otomatis |
| Git + GitHub | Terbaru | Kontrol versi |
| Jira | – | Manajemen backlog Scrum |
| Figma, draw.io / Mermaid | – | Mockup dan diagram |

> Berbeda dengan proyek yang memakai CDN, aset Tailwind dan Alpine di sini dipasang lewat **npm** dan dibangun oleh **Vite**. Aplikasi tidak akan tampil benar sebelum `npm install` dan `npm run dev` (atau `npm run build`) dijalankan.

---

## Fitur Aplikasi

Aplikasi memiliki 4 role: **Surveyor**, **Verifikator**, **Admin**, dan **Pimpinan** (RBAC + pembatasan wilayah tugas). Pimpinan bersifat hanya-baca.

**Semua pengguna**
- Login, sesi dan logout, ganti password (FR-01 s.d. FR-03)
- Dashboard per role (FR-26) dan ekspor laporan Excel/PDF sesuai hak akses (FR-27)

**Admin**
- Kelola data rumah: mendaftarkan dan mengubah data warga dan rumah (FR-10)
- Kelola user (FR-05), role dan wilayah tugas (FR-06), reset password (FR-07)
- Kelola wilayah (FR-08), audit log (FR-09)
- Daftar dan detail rumah, hanya-baca (FR-14, FR-25)
- Rekap dana per triwulan (FR-28) dan ekspor rekap dana (FR-30)

**Surveyor**
- Daftar rumah di wilayah tugas dengan tombol *Isi Survei* (FR-14)
- Form kelayakan 7 komponen (FR-11a) dan unggah foto bukti (FR-11b)
- Simpan draf (FR-12) dan perbaikan survei (FR-13)
- Riwayat status (FR-24), CRUD progres renovasi (FR-22), aturan progres (FR-23), detail rumah (FR-25)
- Heatmap renovasi di wilayah tugas (FR-31)

**Verifikator**
- Daftar dan detail rumah semua wilayah, hanya-baca (FR-14, FR-25)
- Review survei (FR-15), penetapan kategori manual (FR-16), permintaan perbaikan (FR-17), revisi keputusan (FR-18)
- Daftar penerima bantuan (FR-19), pencatatan penyaluran bantuan (FR-20)
- Pemantauan progres dan penetapan status Selesai (FR-21)
- Rekap dana per triwulan (FR-28), ekspor rekap dana (FR-30), heatmap renovasi semua wilayah (FR-31)

**Pimpinan**
- Rekap dana per triwulan (FR-28) dan tabel rumah penerima dana tanpa detail rumah (FR-29)
- Ekspor rekap dana (FR-30) dan heatmap renovasi semua wilayah (FR-31)

**Sistem**
- Kontrol akses berbasis role dan wilayah (FR-04)
- Seluruh perubahan status melalui satu pintu, `StatusWorkflow`, dengan riwayat yang tidak bisa diubah (append-only)

> Status implementasi tiap fitur mengikuti Jira board. Tidak semua fitur di atas sudah tersedia.

---

## Prasyarat

| Kebutuhan | Cek dengan | Catatan |
|---|---|---|
| PHP ≥ 8.3 dengan ekstensi `pdo_pgsql`, `pgsql`, `mbstring`, `openssl`, `gd`/`imagick`, `fileinfo` | `php -v` dan `php -m` | |
| Composer 2.x | `composer -V` | |
| PostgreSQL ≥ 16 | `psql --version` | |
| Node.js (LTS) + npm | `node -v` dan `npm -v` | |
| Git | `git --version` | |
| `memory_limit` PHP ≥ 1 GB untuk seeding wilayah | `php -r "echo ini_get('memory_limit');"` | Default 128M tidak cukup; lihat [Seeding Data Wilayah](#seeding-data-wilayah) | |

Pilihan alat per OS:

| OS | Server lokal | PostgreSQL |
|---|---|---|
| macOS | Laravel Herd | Homebrew (`postgresql@18` atau versi lain ≥ 16) |
| Windows | Laragon (disarankan) atau XAMPP | Installer resmi PostgreSQL |
| Linux (opsional) | `php artisan serve` atau Nginx + PHP-FPM | `apt`/`dnf` |

---

## Instalasi & Setup

Langkah 1–2 dan 6 s.d. 9 sama di semua OS. Langkah yang berbeda dijelaskan per OS.

### Skenario macOS (Herd + Homebrew)

**Langkah 1 — Pasang Laravel Herd** dari situs resmi Herd. Di *Herd → Settings → PHP*, pilih **PHP 8.3** atau lebih baru sebagai default, lalu cek:

```bash
php -v
composer -V
```

**Langkah 2 — Pasang Node.js dan Git** (jika belum), mis. lewat Homebrew:

```bash
brew install node git
```

**Langkah 3 — Pasang dan jalankan PostgreSQL**

```bash
brew install postgresql@18          # atau versi lain ≥ 16
brew services start postgresql@18
```

Paket ini *keg-only*, jadi tambahkan ke PATH (Apple Silicon; di Mac Intel ganti `/opt/homebrew` dengan `/usr/local`):

```bash
echo 'export PATH="/opt/homebrew/opt/postgresql@18/bin:$PATH"' >> ~/.zshrc
source ~/.zshrc
psql --version
```

**Langkah 4 — Buat database.** Homebrew membuat role superuser bernama sama dengan username macOS Anda, tanpa password:

```bash
createdb db_rumahasa
psql -l                              # pastikan db_rumahasa ada di daftar
```

**Langkah 5 — Clone ke folder yang dilayani Herd** (default `~/Herd`):

```bash
cd ~/Herd
git clone https://github.com/MutiaraAdzikaIslami/RumahAsa.git
cd RumahAsa
git checkout develop
```

Situs otomatis tersedia di `http://rumahasa.test` (nama folder dalam huruf kecil). Jika folder berada di luar `~/Herd`, jalankan `herd link rumahasa` dari dalam folder proyek.

Lanjut ke [Langkah Umum](#langkah-umum-semua-os).


### Skenario Windows (Laragon / XAMPP)

**Langkah 1 — Pasang Laragon (Full)** dari situs resmi, atau XAMPP. Pastikan PHP yang dipakai **8.3 atau lebih baru** (di Laragon: menu *PHP → Version*). Pasang juga Composer, Node.js LTS, dan Git for Windows. Buka **Git Bash** atau terminal Laragon untuk semua perintah di bawah.

**Langkah 2 — Aktifkan ekstensi PostgreSQL di PHP.** Buka `php.ini` (Laragon: *Menu → PHP → php.ini*; XAMPP: `C:\xampp\php\php.ini`), pastikan dua baris ini **tidak diawali titik koma**, lalu restart server:

```ini
extension=pdo_pgsql
extension=pgsql
```

Cek:

```bash
php -m | findstr pgsql
```

**Langkah 3 — Pasang PostgreSQL** dengan installer resmi (≥ 16). Catat **password untuk user `postgres`** yang Anda buat saat instalasi, dan biarkan port default `5432`. Pastikan folder `bin` PostgreSQL masuk ke PATH, misalnya `C:\Program Files\PostgreSQL\<versi>\bin`.

**Langkah 4 — Buat database:**

```bash
psql -U postgres -c "CREATE DATABASE db_rumahasa;"
```

**Langkah 5 — Clone ke folder web server:**

```bash
# Laragon
cd C:/laragon/www
# XAMPP: cd C:/xampp/htdocs
git clone https://github.com/MutiaraAdzikaIslami/RumahAsa.git
cd RumahAsa
git checkout develop
```

Di Laragon, jika *Auto Virtual Hosts* aktif, situs tersedia di `http://rumahasa.test`. Untuk XAMPP, atau jika tidak ingin memakai virtual host, gunakan `php artisan serve` (langkah 9).

**Rekomendasi lintas OS:** agar akhir baris tidak berbeda antara Windows dan macOS, jalankan sekali:

```bash
git config --global core.autocrlf input    # macOS/Linux
git config --global core.autocrlf true     # Windows
```

Lanjut ke [Langkah Umum](#langkah-umum-semua-os).

### Skenario Linux (opsional)

```bash
sudo apt update
sudo apt install -y git curl unzip nodejs npm postgresql postgresql-contrib \
  php php-cli php-pgsql php-mbstring php-xml php-curl php-gd php-zip
# Composer: ikuti petunjuk resmi getcomposer.org

sudo systemctl enable --now postgresql
sudo -u postgres psql -c "CREATE USER rumahasa WITH PASSWORD 'ganti_password';"
sudo -u postgres createdb -O rumahasa db_rumahasa
```

Pastikan PHP yang terpasang ≥ 8.3 (`php -v`); jika repositori distro menyediakan versi lebih lama, gunakan repositori PPA/Sury. Lalu clone proyek dan lanjut ke langkah umum. Produksi memakai Ubuntu LTS + Nginx + PHP-FPM + HTTPS.


### Langkah Umum (semua OS)

**Langkah 6 — Pasang dependensi PHP dan JavaScript**

```bash
composer install
npm install
```

**Langkah 7 — Siapkan `.env`**

```bash
cp .env.example .env        # Windows (Git Bash) sama; CMD: copy .env.example .env
php artisan key:generate
```

Lalu isi bagian database sesuai [Konfigurasi .env](#konfigurasi-env).

**Langkah 8 — Migrasi dan seed**

```bash
php artisan migrate
php -d memory_limit=-1 artisan db:seed
```

`-d memory_limit=-1` hanya berlaku untuk perintah itu dan diperlukan karena seeder wilayah memuat data yang besar. Penjelasan lengkapnya ada di [Seeding Data Wilayah](#seeding-data-wilayah). Untuk mengulang dari awal di lokal: `php -d memory_limit=-1 artisan migrate:fresh --seed`.

> Migrasi dan seeder diselesaikan di SCRUM-8 (Migrasi basis data dan seed data awal). Sebelum itu, `migrate` hanya menjalankan migrasi bawaan Laravel.

**Langkah 9 — Jalankan aplikasi**

```bash
# Terminal 1: aset frontend
npm run dev

# Terminal 2: hanya jika TIDAK memakai Herd/Laragon virtual host
php artisan serve
```

Buka `http://rumahasa.test` (Herd/Laragon) atau `http://127.0.0.1:8000` (`artisan serve`). Untuk build produksi: `npm run build`.

---

## Seeding Data Wilayah

Data wilayah (kota, kecamatan, kelurahan) dimuat dari berkas SQL `wilayah.sql` [ISI: lokasi berkas, mis. `database/seeders/data/wilayah.sql`, dan sumber datanya]. Jumlah barisnya besar (puluhan ribu kelurahan/desa), sedangkan PHP CLI secara bawaan membatasi memori 128 MB. Bila seeder membaca seluruh berkas ke memori, prosesnya berhenti dengan galat `Allowed memory size of 134217728 bytes exhausted` dan data wilayah hanya masuk sebagian.

Pilih salah satu cara berikut:

**Opsi 1 — naikkan batas untuk satu perintah (disarankan untuk lokal).**

```bash
php -d memory_limit=1G artisan db:seed --class=WilayahSeeder
# atau tanpa batas:
php -d memory_limit=-1 artisan db:seed --class=WilayahSeeder
```

`1G` cukup aman dan menjaga laptop tetap responsif. `-1` berarti tanpa batas; pakai hanya di mesin lokal.

**Opsi 2 — ubah `php.ini` secara permanen.** Cari berkasnya dengan `php --ini`, ubah `memory_limit = 1024M`, lalu mulai ulang server lokal. PHP untuk CLI dan untuk web bisa memakai `php.ini` yang berbeda, jadi pastikan yang diubah adalah yang ditunjuk `php --ini`. Windows (Laragon): *Menu → PHP → php.ini*.

**Opsi 3 — lewati PHP sama sekali.** `psql` tidak terkena batas memori PHP:

```bash
# macOS
psql -d db_rumahasa -f database/seeders/data/wilayah.sql
# Windows
psql -U postgres -d db_rumahasa -f database/seeders/data/wilayah.sql
```

Jika memakai opsi ini, jangan menjalankan `WilayahSeeder` lagi agar data tidak ganda.

**Memeriksa hasilnya:**

```sql
SELECT tingkat, COUNT(*) FROM wilayah GROUP BY tingkat;
```

Jika jumlahnya jauh lebih sedikit dari data sumber, hapus data wilayah (atau jalankan `migrate:fresh --seed`) dan ulangi dengan batas memori yang lebih besar.

**Catatan:**
- Jangan menaruh `memory_limit=-1` di server produksi atau `.env.example`.
- GitHub menolak berkas di atas 100 MB. Jika `wilayah.sql` mendekati batas itu, [ISI: tim memutuskan memakai Git LFS atau berkas diunduh terpisah].
- [ISI: tingkat Kota/Kabupaten perlu masuk ke enum `wilayah.tingkat` (saat ini Kecamatan, Kelurahan, RW, RT). Konfirmasi dan sesuaikan tabel Struktur Database.]

---

## Konfigurasi .env

`.env` **tidak pernah di-commit**. Yang di-commit hanya `.env.example` (berisi placeholder, bukan rahasia).

```env
APP_NAME=RumahAsa
APP_ENV=local
APP_KEY=                      # diisi otomatis oleh: php artisan key:generate
APP_DEBUG=true
APP_URL=http://rumahasa.test  # artisan serve: http://127.0.0.1:8000
APP_TIMEZONE=Asia/Jakarta     # [ISI: sesuaikan jika tim menetapkan lain]

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=db_rumahasa
DB_USERNAME=                  # macOS Homebrew: username Mac Anda | Windows: postgres
DB_PASSWORD=                  # macOS Homebrew: kosong | Windows: password saat instalasi

SESSION_DRIVER=database       # [ISI: samakan dengan .env.example di repositori]
FILESYSTEM_DISK=local         # foto disimpan di luar folder public

# Kunci enkripsi data pribadi (NIK/No. KK) — [ISI: nama variabel menyusul saat NikEncryptor dibuat]
```

Pastikan baris `DB_*` di `.env.example` tidak berawalan `#` dan nilainya bukan `sqlite`, karena default Laravel memakai SQLite.

---

## Struktur Direktori Proyek (rancangan target)

> **Ini rancangan dari proposal, bukan potret isi repositori saat ini.** Folder dan berkas ditambahkan bertahap sesuai tiket Jira; bagian ini diperbarui saat struktur berubah.

```
RumahAsa/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/                  ← login, logout, ganti password (SCRUM-10..12)
│   │   │   ├── Admin/                 ← user, wilayah, data rumah, audit log, reset password
│   │   │   ├── Surveyor/              ← tabel rumah wilayah tugas, survei, foto, progres renovasi
│   │   │   ├── Verifikator/           ← review, kategori, penerima bantuan, penyaluran
│   │   │   └── Pimpinan/              ← rekap dana dan rumah penerima dana (hanya-baca)
│   │   │       [ISI: tempat controller lintas role (dashboard, detail rumah, heatmap, ekspor)]
│   │   ├── Middleware/                ← kontrol akses role + wilayah (SCRUM-13)
│   │   └── Requests/                  ← validasi form (FormRequest)
│   ├── Models/                        ← Role, User, Wilayah, RumahWarga, SurveiKelayakan,
│   │                                    SurveiKomponen, SurveiFoto, Penilaian, PenerimaBantuan,
│   │                                    ProgresRenovasi, ProgresFoto, RiwayatStatus, AuditLog
│   ├── Policies/
│   │   └── RumahPolicy.php            ← otorisasi akses rumah per role dan wilayah (termasuk Pimpinan)
│   ├── Services/
│   │   ├── StatusWorkflow.php         ← SATU-SATUNYA pintu perubahan status (SCRUM-25)
│   │   ├── FotoStorageService.php     ← simpan dan sajikan foto secara aman
│   │   └── LaporanService.php         ← ekspor Excel/PDF (SCRUM-39, SCRUM-156)
│   └── Support/
│       ├── NikEncryptor.php           ← enkripsi dan penyamaran NIK / No. KK
│       └── AuditLogger.php            ← pencatat audit log
├── database/
│   ├── migrations/                    ← skema tabel (SCRUM-8)
│   ├── seeders/                       ← role, wilayah, komponen penilaian, akun awal, rumah contoh
│   │   └── data/wilayah.sql           ← data wilayah [ISI: lokasi sebenarnya]
│   └── factories/
├── resources/
│   ├── css/app.css                    ← entry Tailwind (Vite)
│   ├── js/app.js                      ← entry Alpine.js dan Leaflet (Vite)
│   └── views/
│       ├── layouts/                   ← layout dasar (SCRUM-9)
│       ├── auth/ · admin/ · surveyor/ · verifikator/ · pimpinan/
│       └── components/                ← komponen Blade bersama
├── routes/
│   ├── web.php                        ← rute umum dan auth
│   └── admin.php · surveyor.php · verifikator.php · pimpinan.php   ← satu berkas per role
├── storage/app/                       ← foto bukti (di luar folder public)
├── tests/
│   ├── Feature/                       ← alur utama (SCRUM-40)
│   └── Unit/
├── .github/pull_request_template.md   ← template Pull Request
├── .env.example                       ← templat konfigurasi (di-commit)
├── vite.config.js
├── package.json
├── composer.json
├── Proposal-RumahAsa.pdf              ← proposal pengembangan produk yang dikumpulkan ke dosen
└── README.md
```

Prinsip lapisan: *controller* tipis, logika bisnis di `Services`, utilitas teknis di `Support`, otorisasi di `Policies`.

---

## Struktur Database (rancangan target)

> Rancangan dari ERD proposal (15 tabel). Tabel ini diperbarui bila migrasi berubah.

| Tabel | Kolom kunci | Keterangan |
|---|---|---|
| `roles` | nama | Kode role huruf kapital: SURVEYOR, VERIFIKATOR, ADMIN, PIMPINAN |
| `users` | role_id, kredensial, password (hash argon2id/bcrypt) | Akun pengguna |
| `wilayah` | parent_id, nama, tingkat, path | Hierarki wilayah (self-reference). Tingkat: Kecamatan, Kelurahan, RW, RT [ISI: konfirmasi tingkat Kota/Kabupaten] |
| `rumah_warga` | kode_registrasi (UNIQUE), wilayah_id, status, latitude, longitude, NIK (terenkripsi) + hash | Data rumah dan pemilik; diisi Admin; soft delete. `status` memuat 8 nilai (lihat Alur Status Rumah) |
| `survei_kelayakan` | rumah_id (UNIQUE) | Satu survei per rumah |
| `komponen_penilaian` | nama, urutan | Master 7 komponen penilaian |
| `opsi_kondisi` | urutan | Pilihan kondisi per komponen |
| `survei_komponen` | – | Hasil penilaian per komponen |
| `survei_foto` | path_file | Foto bukti survei |
| `penilaian` | kategori, catatan, alasan_revisi, is_aktif | Keputusan verifikator (mendukung revisi) |
| `penerima_bantuan` | status_bantuan, tanggal (penyaluran), jenis, nominal, sumber_program, nomor_sk | Penyaluran bantuan; dasar rekap dana |
| `progres_renovasi` | tahap, persentase, tanggal, catatan | Progres renovasi |
| `progres_foto` | – | Foto progres |
| `riwayat_status` | status_dari, status_ke, catatan, aktor | Jejak perubahan status (append-only); juga dasar filter triwulan heatmap |
| `audit_log` | user_id (nullable), aksi, entitas, data_baru | Jejak aktivitas pengguna |

**Relasi utama:** `Role 1—N User` · `Wilayah 1—N Wilayah` (hierarki) · `Wilayah 1—N RumahWarga` · `RumahWarga 1—1 SurveiKelayakan` · `SurveiKelayakan 1—N SurveiKomponen/SurveiFoto` · `RumahWarga 1—N Penilaian/PenerimaBantuan/ProgresRenovasi/RiwayatStatus` · `ProgresRenovasi 1—N ProgresFoto`.

---

## Alur Status Rumah

```
Menunggu Disurvei → Draf → Diajukan → (Perlu Perbaikan ⇄ Diajukan) → Layak Huni
                                                                    ↘ Penerima Bantuan → Dalam Renovasi → Selesai
```

Setiap perubahan status **wajib** lewat `StatusWorkflow` agar tercatat di `riwayat_status` dan `audit_log`. Dilarang mengubah kolom `status` langsung dari controller.

| Operasi | Dari → Ke | Dipicu oleh |
|---|---|---|
| `daftarkan` | (baru) → Menunggu Disurvei | Admin, saat menyimpan rumah baru |
| `mulaiSurvei` | Menunggu Disurvei → Draf | Surveyor, otomatis saat draf survei pertama disimpan |
| `ajukan` | Draf / Perlu Perbaikan → Diajukan | Surveyor |
| `mintaPerbaikan` | Diajukan → Perlu Perbaikan | Verifikator, catatan wajib |
| `tetapkanKategori` | Diajukan → Layak Huni / Penerima Bantuan | Verifikator, catatan wajib |
| `revisiKeputusan` | Layak Huni ⇄ Penerima Bantuan | Verifikator, alasan wajib, hanya sebelum renovasi dimulai |
| `mulaiRenovasi` | Penerima Bantuan → Dalam Renovasi | Verifikator, saat penyaluran bantuan dicatat |
| `selesaikan` | Dalam Renovasi → Selesai | Verifikator, hanya jika progres terakhir 100% |

Admin hanya boleh mengubah data rumah selama statusnya **Menunggu Disurvei** atau **Draf**.

---

## Glosarium

Gunakan istilah ini secara seragam di kode, tampilan, Jira, dan pesan commit.

| Istilah | Arti |
|---|---|
| Menunggu Disurvei | Rumah sudah didata Admin dan belum ada survei |
| Draf | Survei sudah mulai diisi Surveyor tetapi belum diajukan |
| Diajukan | Survei lengkap menunggu review Verifikator |
| Perlu Perbaikan | Verifikator meminta survei diperbaiki, disertai catatan |
| Layak Huni | Keputusan: rumah layak huni, tidak diproses sebagai penerima bantuan |
| Penerima Bantuan | Keputusan: rumah tidak layak huni dan ditetapkan sebagai penerima bantuan [ISI: konfirmasi pemakaian istilah "Tidak Layak Huni" di tiket lama] |
| Dalam Renovasi | Bantuan sudah disalurkan dan renovasi berjalan |
| Selesai | Renovasi selesai (progres terakhir 100%) |
| Wilayah tugas | Wilayah yang ditetapkan Admin untuk seorang Surveyor |
| NIK tersamar | NIK yang sebagian digitnya ditutup saat ditampilkan |
| Triwulan | Jan–Mar, Apr–Jun, Jul–Sep, Okt–Des. Rekap dana memakai tanggal penyaluran; filter heatmap memakai tanggal rumah masuk status saat ini |
| Sumber program | Asal dana bantuan pada penyaluran |
| Lapisan heatmap | Menunggu Disurvei; Sedang Disurvei (Draf, Diajukan, Perlu Perbaikan); Sedang Renovasi (Dalam Renovasi); Selesai Renovasi (Selesai) |

---

## Panduan Penggunaan per Role

**Akun awal:** [ISI: tabel username akun contoh per role dari seeder. Jangan menulis password asli di README.]

| Fitur | Surveyor | Verifikator | Admin | Pimpinan |
|---|---|---|---|---|
| Kelola data rumah | – | – | ✔ | – |
| Lihat daftar dan detail rumah | ✔ (wilayah tugas) | ✔ | ✔ | – |
| Isi dan ajukan survei | ✔ | – | – | – |
| Review, kategori, revisi keputusan | – | ✔ | – | – |
| Penyaluran bantuan dan progres renovasi | ✔ (progres) | ✔ | – | – |
| Rekap dana triwulan | – | ✔ | ✔ | ✔ |
| Daftar rumah penerima dana | – | – | – | ✔ |
| Heatmap renovasi | ✔ (wilayah tugas) | ✔ | – | ✔ |
| User, wilayah, audit log | – | – | ✔ | – |

### Admin
1. Daftarkan rumah warga lewat *Kelola Data Rumah*. Rumah baru berstatus **Menunggu Disurvei**; data hanya bisa diubah selama statusnya Menunggu Disurvei atau Draf.
2. Kelola akun: buat user, tetapkan role dan wilayah tugas Surveyor, reset password.
3. Kelola hierarki wilayah dan pantau **Audit Log**.
4. Lihat rekap dana triwulan dan ekspor bila diperlukan.

### Surveyor
1. Login; tabel rumah di wilayah tugas Anda langsung tampil. Saring berdasarkan lokasi (mis. kelurahan) agar beberapa rumah bisa dikunjungi sekali jalan.
2. Tekan **Isi Survei** pada rumah yang akan disurvei. Isi form kelayakan 7 komponen dan unggah foto bukti (JPG/PNG/WebP, maks. 5 MB).
3. Simpan sebagai **Draf** (status berubah dari Menunggu Disurvei menjadi Draf), lalu **Ajukan** bila sudah lengkap.
4. Bila status **Perlu Perbaikan**, baca catatan verifikator, perbaiki, lalu ajukan ulang.
5. Setelah rumah masuk tahap renovasi, catat progres (tahap, persentase, foto) dan lihat heatmap wilayah tugas.

### Verifikator
1. Lihat data rumah semua wilayah (hanya-baca), lalu buka rumah berstatus **Diajukan** dan review survei beserta foto.
2. Tetapkan kategori (Layak Huni atau Penerima Bantuan), atau **Minta Perbaikan** dengan catatan yang jelas. Keputusan bisa direvisi selama renovasi belum dimulai.
3. Untuk rumah **Penerima Bantuan**, catat penyaluran (jenis, nominal, sumber program, nomor SK); status berubah menjadi **Dalam Renovasi**.
4. Pantau progres renovasi, dan tetapkan **Selesai** bila progres terakhir 100%.
5. Lihat rekap dana, ekspor, dan heatmap semua wilayah.

### Pimpinan
1. Lihat rekap dana per triwulan (filter tahun, triwulan, wilayah, sumber program).
2. Buka tabel rumah penerima dana (NIK tersamar; tanpa detail rumah).
3. Ekspor rekap dana dan lihat heatmap semua wilayah. Semua akses bersifat hanya-baca.

---

## Aturan Branching & Alur Kerja Git

| Branch | Fungsi | Siapa yang boleh push |
|---|---|---|
| `main` | Versi stabil / demo | Tidak ada push langsung (branch protection aktif, push ditolak dengan galat GH006); hanya merge Pull Request (PR) dari `develop` di akhir sprint, atau PR khusus untuk berkas dokumen |
| `develop` | Integrasi seluruh fitur | Tidak ada push langsung; hanya merge Pull Request (PR) dari branch fitur |
| `feature/scrum-<nomor>-<slug>` | Satu per **tiket induk** Jira | Anggota yang mengerjakan sub-task tiket itu |

**Penamaan:** `feature/scrum-` + nomor Jira asli + slug huruf kecil dipisah tanda hubung, contoh `feature/scrum-10-login`. Nomor yang dipakai adalah nomor Jira, bukan urutan fase.
**Pengecualian awal proyek:** pekerjaan inisiasi (SCRUM-7, SCRUM-8, SCRUM-9) dikerjakan langsung di `main` dan tidak memiliki branch fitur. Mulai SCRUM-10 semua pekerjaan memakai branch fitur.

**Alur satu sub-task:**
1. Pindah ke branch tiket induknya: `git checkout feature/scrum-<n>-<slug>`.
2. Sinkronkan: `git fetch origin && git merge origin/develop`.
3. Kerjakan sub-task, commit kecil dan sering (lihat konvensi di bawah).
4. Sebelum push, tarik perubahan rekan di branch yang sama: `git pull origin feature/scrum-<n>-<slug>`.
5. Push, lalu buka Pull Request ke `develop` bila seluruh sub-task tiket induk selesai.

**Rilis:** di akhir sprint, buka Pull Request (PR) `develop` → `main`, lalu beri tag, mis. `git tag v0.1.0-sprint0 && git push origin v0.1.0-sprint0`.

**Dilarang:** `git push --force` ke `main`/`develop`, commit langsung ke keduanya, dan meng-commit `.env`, `vendor/`, `node_modules/`.

---

## Konvensi Pesan Commit

Format:

```
<jenis>: <deskripsi singkat>
```

Contoh: `feat: tambah halaman login`

### Jenis commit

| Jenis | Dipakai untuk | Contoh |
|---|---|---|
| `feat` | Fitur atau fungsionalitas baru | `feat: tambah form tambah dan ubah user` |
| `fix` | Memperbaiki bug atau galat | `fix: perbaiki galat saat unggah foto bukti` |
| `refactor` | Mengubah struktur kode tanpa mengubah perilaku | `refactor: pindahkan logika ubah status ke StatusWorkflow` |
| `perf` | Meningkatkan performa atau kecepatan | `perf: optimalkan query daftar rumah` |
| `style` | Format kode saja (spasi, titik koma, hasil Pint), tanpa mengubah logika | `style: rapikan kode dengan Pint` |
| `ui` | Perubahan tampilan saja (Blade, CSS, Tailwind), tanpa logika baru | `ui: ubah warna tombol utama` |
| `docs` | Dokumentasi: README, komentar kode | `docs: tambah panduan seeding wilayah` |
| `test` | Menambah atau memperbaiki pengujian | `test: tambah uji transisi StatusWorkflow` |
| `chore` | Pemeliharaan rutin yang tidak menyentuh kode aplikasi atau tes (`.gitignore`, `.env.example`, penamaan berkas) | `chore: tambah folder storage ke .gitignore` |
| `build` | Sistem build atau dependensi (`composer.json`, `package.json`, `vite.config.js`) | `build: pasang Leaflet lewat npm` |
| `ci` | Konfigurasi atau skrip CI/CD (mis. GitHub Actions) | `ci: tambah workflow uji otomatis` |
| `revert` | Membatalkan commit sebelumnya | `revert: batalkan penambahan filter triwulan` |

Cara cepat memilih: menambah kemampuan baru → `feat`; memperbaiki perilaku yang salah → `fix`; mengubah struktur tanpa mengubah perilaku → `refactor`; hanya tampilan → `ui`; hanya format → `style`. Migrasi dan seeder mengikuti jenis perubahannya (tabel baru untuk fitur memakai `feat`, perbaikan skema memakai `fix`).

### Aturan penulisan

1. Jenis ditulis huruf kecil, diikuti titik dua dan satu spasi.
2. Deskripsi berbahasa Indonesia, diawali kata kerja, huruf kecil, tanpa titik di akhir. Nama kelas, berkas, dan istilah teknis tidak diterjemahkan (`StatusWorkflow`, `middleware`).
3. Maksimal 72 karakter pada baris pertama.
4. Satu commit memuat satu perubahan logis. Jangan menggabungkan `feat` dan `fix`, atau perubahan kode dengan perapian format.
5. Nomor tiket tidak ditulis di pesan commit. Nomor tiket sudah ada di nama branch dan judul Pull Request.
6. Penjelasan tambahan (alasan perubahan, bukan isinya) boleh ditulis di badan commit setelah satu baris kosong.
7. Pesan yang dilarang karena tidak bermakna: `update`, `fix bug`, `perbaikan`, `tes`, `wip`, `asdf`. Tulis apa yang berubah.
8. `revert` memakai format `revert: batalkan <deskripsi commit yang dibatalkan>`.
9. Commit merge dari Pull Request dibuat otomatis oleh GitHub (merge commit) dan tidak diubah.
10. Jenis `Initial commit:` hanya dipakai pada pekerjaan inisiasi proyek di `main` (SCRUM-7 dan SCRUM-8) dan tidak dipakai lagi setelah branch fitur dimulai.

### Contoh benar dan salah

| Salah | Benar | Alasan |
|---|---|---|
| `update login` | `feat: tambah autentikasi username dan password` | Tidak ada jenis dan tidak menjelaskan perubahan |
| `Feat: Tambah Halaman Login.` | `feat: tambah halaman login` | Huruf kecil dan tanpa titik |
| `feat(scrum-65): tambah halaman login` | `feat: tambah halaman login` | Nomor tiket tidak ditulis di pesan |
| `fix: perbaiki upload dan ubah warna tombol` | dua commit: `fix: perbaiki unggah foto` dan `ui: ubah warna tombol` | Satu commit satu perubahan |
| `feat: rapikan indentasi` | `style: rapikan indentasi` | Jenis tidak sesuai isi |

---

## Pull Request & Review

- Tujuan Pull Request (PR): `develop` (kecuali rilis sprint ke `main`).
- Judul Pull Request (PR): `<SCRUM-<nomor>>: <judul tiket induk>`.
- Deskripsi minimal: tautan tiket Jira, ringkasan perubahan, cara menguji, dan tangkapan layar bila ada perubahan tampilan.
- Minimal **1 reviewer** selain pembuat Pull Request (PR); konflik diselesaikan oleh pembuat Pull Request (PR).
- Metode penggabungan: **Merge commit** (bukan Squash), agar kontribusi tiap anggota pada branch yang sama tetap tercatat.
- Setelah merge, status tiket di Jira dipindahkan sesuai Definition of Done.

---

### Template Pull Request

Simpan sebagai `.github/pull_request_template.md`. Agar terbaca otomatis, berkas ini harus berada di branch utama repositori.

```markdown
## Tiket
SCRUM-<nomor>: <judul tiket induk>
Tautan Jira: <url>

## Ringkasan perubahan
-

## Cara menguji
1.
2.

## Tangkapan layar (jika ada perubahan tampilan)

## Checklist
- [ ] Seluruh sub-task tiket induk selesai dan kriteria di Jira terpenuhi
- [ ] `php artisan migrate:fresh --seed` berjalan tanpa error
- [ ] `php artisan test` lulus
- [ ] `./vendor/bin/pint` sudah dijalankan
- [ ] Pesan commit sesuai konvensi
- [ ] Perubahan status rumah hanya lewat `StatusWorkflow`
- [ ] Tidak ada `.env`, `vendor/`, `node_modules/`, atau data warga asli
- [ ] Perubahan struktur direktori atau database sudah tercermin di README
```

---

## Definition of Done

Sebuah tiket dinyatakan selesai bila:

1. Seluruh sub-task selesai dan kriteria penerimaan di Jira terpenuhi.
2. Kode berjalan di macOS **dan** Windows (minimal dicoba satu anggota per OS untuk perubahan setup).
3. `php -d memory_limit=-1 artisan migrate:fresh --seed` berjalan tanpa error.
4. Pengujian otomatis terkait lulus, dan tidak ada pengujian lama yang rusak.
5. Gaya kode sudah dirapikan (`./vendor/bin/pint`).
6. Pesan commit mengikuti [Konvensi Pesan Commit](#konvensi-pesan-commit).
7. Perubahan struktur direktori/database sudah tercermin di README.
8. Pull Request (PR) sudah di-review dan di-merge ke `develop`.

---

## Standar Kode & Pengujian

- **Gaya kode:** PSR-12 melalui Laravel Pint (`./vendor/bin/pint`). Nama kelas `PascalCase`, metode `camelCase`, tabel dan kolom `snake_case`. Nama domain memakai istilah Indonesia sesuai ERD (mis. `RumahWarga`).
- **Lapisan:** controller tipis, validasi di `FormRequest`, logika bisnis di `app/Services`, otorisasi di `Policies`.
- **Pengujian:** `php artisan test`. Prioritas pada alur utama (login, input survei, review, perubahan status, penerima bantuan, progres) di SCRUM-40.
- **Migrasi, seeder, dan rute:** ikuti [Aturan Migrasi, Seeder, dan Rute](#aturan-migrasi-seeder-dan-rute).
- **Lintas OS:** gunakan `DIRECTORY_SEPARATOR`/helper path Laravel, jangan menulis path absolut khusus OS; perhatikan huruf besar-kecil nama berkas (Linux/produksi membedakannya, macOS/Windows umumnya tidak).

---

## Aturan Migrasi, Seeder, dan Rute

Berkas-berkas ini paling sering menimbulkan konflik saat beberapa orang bekerja paralel, jadi aturannya dibuat ketat.

### Migrasi
1. Jangan mengubah migrasi yang sudah ada di `develop`; buat migrasi baru (mis. `tambah_status_menunggu_disurvei_ke_rumah_warga_table`).
2. Beri nama deskriptif: `buat_<tabel>_table` atau `tambah_<kolom>_ke_<tabel>_table`.
3. Satu migrasi satu tujuan, dan selalu isi `down()`.
4. Jalankan `git pull` sebelum `php artisan make:migration` supaya cap waktu tidak bertabrakan. Setelah merge, cek `php artisan migrate:status`.
5. Nilai status rumah didefinisikan di satu tempat [ISI: nama kelas/enum, disepakati di SCRUM-25] dan dipakai semua modul.

### Seeder
1. `DatabaseSeeder` memanggil seeder dalam urutan: role, wilayah, komponen penilaian dan opsi kondisi, user contoh, lalu rumah contoh.
2. Seeder harus **idempoten** (`firstOrCreate`/`updateOrCreate`) agar dijalankan dua kali tidak menggandakan data.
3. Rumah contoh minimal satu per status (8 status). Rumah berstatus Penerima Bantuan sampai Selesai memiliki data penyaluran, dan yang Dalam Renovasi dan Selesai memiliki progres. Riwayat status dibuat lewat `StatusWorkflow`, tidak diisi langsung.
4. Rumah contoh hanya dijalankan di lingkungan `local` dan `testing`, dan hanya berisi data dummy.
5. Password akun contoh tidak ditulis di README atau kode; dibaca dari `.env` [ISI: nama variabel disepakati].
6. Data wilayah mengikuti [Seeding Data Wilayah](#seeding-data-wilayah).

### Rute dan menu
1. `routes/web.php` hanya memuat rute umum dan auth. Rute per role berada di `routes/admin.php`, `surveyor.php`, `verifikator.php`, dan `pimpinan.php`, dimuat lewat `bootstrap/app.php`.
2. Setiap berkas rute memakai prefiks URL dan nama rute sesuai role (`admin.*`, `surveyor.*`, `verifikator.*`, `pimpinan.*`) dan middleware role.
3. Menu navigasi didefinisikan di satu berkas konfigurasi [ISI: mis. `config/navigation.php`], dan tiap anggota hanya menambah baris untuk fiturnya sendiri.
4. Setelah menambah rute, jalankan `php artisan route:list` untuk memastikan tidak ada nama atau URL ganda.

---

## Keamanan & Data Pribadi

Aplikasi memuat data pribadi warga, sehingga tunduk pada **UU No. 27 Tahun 2022 tentang Pelindungan Data Pribadi**.

- `.env`, kunci, dan kredensial tidak boleh masuk ke Git. Jika terlanjur ter-commit, segera ganti kuncinya dan beri tahu tim.
- NIK dan No. KK dienkripsi (AES-256-GCM) dan ditampilkan ter-masking; jangan dicatat ke log.
- Password di-hash (argon2id/bcrypt); tidak pernah disimpan atau dikirim sebagai teks biasa.
- Foto disimpan di luar folder publik dan disajikan lewat endpoint yang butuh autentikasi; unggahan divalidasi (JPG/PNG/WebP, maks. 5 MB).
- Gunakan **data dummy** untuk pengembangan, demo, dan tangkapan layar; jangan memakai data warga sungguhan.
- Penghapusan data memakai soft delete; `riwayat_status` dan `audit_log` bersifat append-only.
- Pimpinan hanya melihat NIK tersamar dan tidak memiliki akses ke detail rumah; heatmap tidak boleh menampilkan data pribadi.
- Akses Surveyor dibatasi wilayah tugasnya di tingkat query (Policy dan query scope), bukan hanya menyembunyikan menu.

---

## Mengatasi Error IDE

Jika VS Code (Intelephense) menandai merah pada fasad atau Eloquent padahal kode berjalan normal, itu umumnya error IDE, bukan error aplikasi.

1. Jalankan `composer install` agar folder `vendor/` ada, lalu *Reload Window*.
2. Jika tim memasang `barryvdh/laravel-ide-helper` (**[ISI: belum dipastikan terpasang]**):
   ```bash
   php artisan ide-helper:generate
   php artisan ide-helper:models --nowrite
   ```
3. Pastikan interpreter PHP di IDE menunjuk ke PHP ≥ 8.3 (macOS: PHP dari Herd; Windows: PHP Laragon/XAMPP).

---

## Troubleshooting

### ❌ `php: command not found` atau `php` menunjuk ke versi yang sudah dihapus (macOS/Herd)
Penyebab umum: tautan simbolik `php` mengarah ke versi PHP Herd yang telah dihapus. Atur ulang di *Herd → Settings → PHP* ke 8.3+, atau:
```bash
ln -sf "$HOME/Library/Application Support/Herd/bin/php83" "$HOME/Library/Application Support/Herd/bin/php"
hash -r
php -v
```

### ❌ `could not find driver`
Ekstensi `pdo_pgsql` belum aktif. Windows: hapus tanda `;` pada `extension=pdo_pgsql` dan `extension=pgsql` di `php.ini`, lalu restart server. macOS/Linux: cek `php -m | grep pgsql`; di Linux pasang `php-pgsql`.

### ❌ `SQLSTATE[08006] Connection refused` / tidak bisa terhubung
PostgreSQL belum berjalan atau port berbeda. macOS: `brew services list` lalu `brew services start postgresql@18`. Windows: buka *Services* dan mulai layanan `postgresql-x64-<versi>`. Cek juga `DB_HOST` dan `DB_PORT` di `.env`.

### ❌ `FATAL: password authentication failed` / `role does not exist`
`DB_USERNAME`/`DB_PASSWORD` tidak cocok. macOS Homebrew: username = username Mac, password kosong. Windows: user `postgres` dengan password dari saat instalasi.

### ❌ `database "db_rumahasa" does not exist`
Buat database dulu (Langkah 4), dan pastikan nama di `.env` sama persis.

### ❌ Aplikasi memakai SQLite, bukan PostgreSQL
`DB_CONNECTION` di `.env` masih `sqlite` atau baris `DB_*` masih dikomentari. Ubah ke `pgsql`, lalu `php artisan config:clear`.

### ❌ `No application encryption key has been specified`
Jalankan `php artisan key:generate`.

### ❌ `Vite manifest not found` atau tampilan tanpa gaya
Aset belum dibangun. Jalankan `npm install` lalu `npm run dev` (pengembangan) atau `npm run build`. Pastikan versi Node.js memenuhi syarat Vite.

### ❌ Perubahan `.env` atau konfigurasi tidak berpengaruh
```bash
php artisan config:clear
php artisan cache:clear
```

### ❌ `Permission denied` pada `storage/` atau `bootstrap/cache` (macOS/Linux)
```bash
chmod -R ug+rw storage bootstrap/cache
```

### ❌ Peringatan `LF will be replaced by CRLF` (Windows)
Normal. Atur `core.autocrlf` seperti di Langkah Windows agar akhir baris konsisten.

### ❌ `composer install` kehabisan memori atau gagal
Jalankan `COMPOSER_MEMORY_LIMIT=-1 composer install` (macOS/Linux) atau set variabel yang sama di Windows, dan pastikan ekstensi PHP yang dibutuhkan aktif.

### ❌ Port 5432 sudah dipakai
Ada instance PostgreSQL lain (mis. dua versi terpasang). Hentikan salah satu, atau ubah port dan samakan `DB_PORT`.

### ❌ `Allowed memory size of 134217728 bytes exhausted` saat seeding
Batas memori PHP CLI (128 MB) tidak cukup untuk seeder wilayah, sehingga proses berhenti dan data wilayah hanya masuk sebagian. Jalankan ulang dengan `php -d memory_limit=1G artisan db:seed --class=WilayahSeeder` (atau `-1` di lokal), atau muat berkas dengan `psql -f`. Lihat [Seeding Data Wilayah](#seeding-data-wilayah) untuk opsi permanen dan cara memeriksa jumlah baris.

---

## Perintah yang Sering Digunakan

```bash
# Laravel
php artisan serve                       # server dev tanpa Herd/Laragon
php artisan migrate                     # jalankan migrasi
php artisan migrate:fresh --seed        # reset DB + isi data awal (HANYA di lokal)
php artisan db:seed                     # jalankan seeder
php artisan route:list                  # daftar rute
php artisan config:clear && php artisan cache:clear
php artisan test                        # jalankan pengujian
./vendor/bin/pint                       # rapikan gaya kode

# Frontend
npm install                             # pasang dependensi
npm run dev                             # dev server Vite
npm run build                           # build produksi

# PostgreSQL
psql -l                                 # daftar database
psql -d db_rumahasa                    # masuk ke database

# Git
git fetch --all --prune
git checkout feature/scrum-<n>-<slug>
git merge origin/develop
git status
git log --oneline --graph --all

# Seeding wilayah
php -d memory_limit=1G artisan db:seed --class=WilayahSeeder
psql -d db_rumahasa -f database/seeders/data/wilayah.sql
php --ini                               # lokasi php.ini yang dipakai CLI
```

---

## Lisensi

[Lisensi ditetapkan tim. Proyek ini dikembangkan untuk keperluan akademik mata kuliah BBK3EAB3 - PROYEK PERANGKAT LUNAK Semester Ganjil 2026/2027.]
