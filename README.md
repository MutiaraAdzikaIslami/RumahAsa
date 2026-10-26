# RumahAsa — Sistem Pendataan Digital Rumah Tidak Layak Huni (RTLH)

RumahAsa adalah aplikasi digital berbasis web untuk mendata, menilai kelayakan, menetapkan penerima bantuan, dan memantau renovasi Rumah Tidak Layak Huni (RTLH), sebagai pendukung **SDG 11 (Kota dan Permukiman yang Berkelanjutan)**. Dikembangkan oleh **Kelompok 5**, S1 Sistem Informasi - Kampus Surabaya, Fakultas Rekayasa Industri, Universitas Telkom, untuk mata kuliah **BBK3EAB3 - Proyek Perangkat Lunak** (Dosen Pengampu: Dr. Berlian Rahmy Lidiawaty, S.ST., M.MT.).

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
5. [Konfigurasi .env](#konfigurasi-env)
6. [Struktur Direktori Proyek](#struktur-direktori-proyek-rancangan-target)
7. [Struktur Database](#struktur-database-rancangan-target)
8. [Alur Status Rumah](#alur-status-rumah)
9. [Panduan Penggunaan per Role](#panduan-penggunaan-per-role)
10. [Aturan Branching & Alur Kerja Git](#aturan-branching--alur-kerja-git)
11. [Konvensi Commit](#konvensi-commit)
12. [Pull Request & Review](#pull-request--review)
13. [Definition of Done](#definition-of-done)
14. [Standar Kode & Pengujian](#standar-kode--pengujian)
15. [Keamanan & Data Pribadi](#keamanan--data-pribadi)
16. [Mengatasi Error IDE](#mengatasi-error-ide)
17. [Troubleshooting](#troubleshooting)
18. [Perintah yang Sering Digunakan](#perintah-yang-sering-digunakan)
19. [Lisensi](#lisensi)

---

## Teknologi yang Digunakan

| Teknologi | Versi | Peran |
|---|---|---|
| Laravel | 13.x (saat ini 13.37.0) | Framework backend (arsitektur monolit modular berlapis / MVC) |
| PHP | 8.3 atau lebih baru | Bahasa pemrograman server |
| PostgreSQL | 16 atau lebih baru (anggota tim memakai versi berbeda, mis. 18) | Basis data utama |
| Blade | Bawaan Laravel | Templating tampilan |
| Tailwind CSS | [ISI: cek dengan `npm ls tailwindcss`] | Styling antarmuka |
| Alpine.js | [ISI: cek dengan `npm ls alpinejs`] | Interaksi ringan di sisi klien |
| Vite + npm | [ISI: versi Node.js yang disepakati tim] | Build dan dev server aset frontend |
| Composer | 2.x | Manajer paket PHP |
| Laravel Excel | [ISI: setelah dipasang] | Ekspor laporan Excel |
| DomPDF | [ISI: setelah dipasang] | Ekspor laporan PDF |
| PHPUnit / Pest | [ISI: sesuai yang dipakai tim] | Pengujian otomatis |
| Git + GitHub | Terbaru | Kontrol versi |
| Jira | – | Manajemen backlog Scrum |
| Figma, draw.io / Mermaid | – | Mockup dan diagram |

> Berbeda dengan proyek yang memakai CDN, aset Tailwind dan Alpine di sini dipasang lewat **npm** dan dibangun oleh **Vite**. Aplikasi tidak akan tampil benar sebelum `npm install` dan `npm run dev` (atau `npm run build`) dijalankan.

---

## Fitur Aplikasi

Aplikasi memiliki 3 role: **Surveyor**, **Verifikator**, **Admin** (RBAC + pembatasan wilayah tugas).

**Semua pengguna**
- Login, sesi dan logout, ganti password (FR-01 s.d. FR-03)
- Daftar rumah (FR-14), dashboard per role (FR-26), ekspor laporan Excel/PDF (FR-27)

**Surveyor**
- Input data rumah (FR-10)
- Form kelayakan 7 komponen (FR-11a) dan unggah foto bukti (FR-11b)
- Simpan draf (FR-12) dan perbaikan survei (FR-13)
- Riwayat status (FR-24), CRUD progres renovasi (FR-22), aturan progres (FR-23), detail rumah (FR-25)

**Verifikator**
- Review survei (FR-15), penetapan kategori manual (FR-16), permintaan perbaikan (FR-17), revisi keputusan (FR-18)
- Daftar penerima bantuan (FR-19), pencatatan penyaluran bantuan (FR-20)
- Pemantauan progres dan penetapan status Selesai (FR-21), detail rumah (FR-25)

**Admin**
- Kelola user (FR-05), role dan wilayah tugas (FR-06), reset password (FR-07)
- Kelola wilayah (FR-08), audit log (FR-09)

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
php artisan db:seed         # tersedia setelah Scrum / SCRUM-8 selesai
```

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

> **Ini rancangan dari proposal, bukan potret isi repositori saat ini.** Folder dan berkas ditambahkan bertahap sesuai tiket Jira; perbarui bagian ini saat struktur berubah.

```
RumahAsa/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/                  ← login, logout, ganti password (SCRUM-10..12)
│   │   │   ├── Admin/                 ← user, wilayah, audit log, reset password
│   │   │   ├── Surveyor/              ← rumah, survei, foto, progres renovasi
│   │   │   └── Verifikator/           ← review, kategori, penerima bantuan
│   │   ├── Middleware/                ← kontrol akses role + wilayah (SCRUM-13)
│   │   └── Requests/                  ← validasi form (FormRequest)
│   ├── Models/                        ← Role, User, Wilayah, RumahWarga, SurveiKelayakan,
│   │                                    SurveiKomponen, SurveiFoto, Penilaian, PenerimaBantuan,
│   │                                    ProgresRenovasi, ProgresFoto, RiwayatStatus, AuditLog
│   ├── Policies/
│   │   └── RumahPolicy.php            ← otorisasi akses rumah per role dan wilayah
│   ├── Services/
│   │   ├── StatusWorkflow.php         ← SATU-SATUNYA pintu perubahan status (SCRUM-25)
│   │   ├── FotoStorageService.php     ← simpan dan sajikan foto secara aman
│   │   └── LaporanService.php         ← ekspor Excel/PDF (SCRUM-39)
│   └── Support/
│       ├── NikEncryptor.php           ← enkripsi dan masking NIK / No. KK
│       └── AuditLogger.php            ← pencatat audit log
├── database/
│   ├── migrations/                    ← skema tabel (SCRUM-8)
│   ├── seeders/                       ← role, wilayah, komponen penilaian, akun awal
│   └── factories/
├── resources/
│   ├── css/app.css                    ← entry Tailwind (Vite)
│   ├── js/app.js                      ← entry Alpine.js (Vite)
│   └── views/
│       ├── layouts/                   ← layout desktop (SCRUM-9)
│       ├── auth/ · admin/ · surveyor/ · verifikator/
│       └── components/                ← komponen Blade bersama
├── routes/web.php                     ← rute dikelompokkan per role
├── storage/app/                       ← foto bukti (di luar folder public)
├── tests/
│   ├── Feature/                       ← alur utama (SCRUM-40)
│   └── Unit/
├── .env.example                       ← templat konfigurasi (di-commit)
├── vite.config.js
├── package.json
└── composer.json
```

Prinsip lapisan: *controller* tipis, logika bisnis di `Services`, utilitas teknis di `Support`, otorisasi di `Policies`.

---

## Struktur Database (rancangan target)

> Rancangan dari ERD proposal (15 tabel). Tipe data dan kolom lengkap ditetapkan pada **SCRUM-8**; tabel ini diperbarui setelah migrasi selesai.

| Tabel | Kolom kunci | Keterangan |
|---|---|---|
| `roles` | nama | Surveyor, Verifikator, Admin |
| `users` | role_id, kredensial, password (hash argon2id/bcrypt) | Akun pengguna |
| `wilayah` | parent_id, tingkat, path | Hierarki Kecamatan → Kelurahan → RW → RT (self-reference) |
| `rumah_warga` | kode_registrasi (UNIQUE), wilayah_id, status, latitude, longitude, NIK (terenkripsi) + hash | Data rumah dan pemilik; soft delete |
| `survei_kelayakan` | rumah_id (UNIQUE) | Satu survei per rumah |
| `komponen_penilaian` | – | Master 7 komponen penilaian |
| `opsi_kondisi` | – | Pilihan kondisi per komponen |
| `survei_komponen` | – | Hasil penilaian per komponen |
| `survei_foto` | path_file | Foto bukti survei |
| `penilaian` | kategori, catatan, alasan_revisi, is_aktif | Keputusan verifikator (mendukung revisi) |
| `penerima_bantuan` | status_bantuan, tanggal, jenis, nominal, nomor_sk | Penyaluran bantuan |
| `progres_renovasi` | tahap, persentase, tanggal, catatan | Progres renovasi |
| `progres_foto` | – | Foto progres |
| `riwayat_status` | status_dari, status_ke, catatan, aktor | Jejak perubahan status (append-only) |
| `audit_log` | user_id (nullable), aksi, entitas, data_baru | Jejak aktivitas pengguna |

**Relasi utama:** `Role 1—N User` · `Wilayah 1—N Wilayah` (hierarki) · `Wilayah 1—N RumahWarga` · `RumahWarga 1—1 SurveiKelayakan` · `SurveiKelayakan 1—N SurveiKomponen/SurveiFoto` · `RumahWarga 1—N Penilaian/PenerimaBantuan/ProgresRenovasi/RiwayatStatus` · `ProgresRenovasi 1—N ProgresFoto`.

---

## Alur Status Rumah

```
Draf → Diajukan → (Perlu Perbaikan ⇄ Diajukan) → Layak Huni → Penerima Bantuan → Dalam Renovasi → Selesai
```

Setiap perubahan status **wajib** lewat `StatusWorkflow` agar tercatat di `riwayat_status` dan `audit_log`. Dilarang mengubah kolom `status` langsung dari controller.

---

## Panduan Penggunaan per Role

**Akun awal:** [ISI: tabel akun default dibuat setelah seeder SCRUM-8 selesai. Jangan menulis password asli di README.]

### Surveyor
1. Login, lalu buka daftar rumah dan pilih *Tambah Rumah* (hanya untuk wilayah tugas Anda).
2. Isi data rumah, lalu isi form kelayakan 7 komponen dan unggah foto bukti (JPG/PNG/WebP, maks. 5 MB).
3. Simpan sebagai **Draf**, atau **Ajukan** jika sudah lengkap.
4. Bila status **Perlu Perbaikan**, baca catatan verifikator, perbaiki, lalu ajukan ulang.
5. Setelah rumah masuk tahap renovasi, catat progres (tahap, persentase, foto).

### Verifikator
1. Buka rumah berstatus **Diajukan** dan review survei beserta foto.
2. Tetapkan kategori, atau **Minta Perbaikan** dengan catatan yang jelas.
3. Untuk rumah **Layak Huni** yang terpilih, catat penyaluran bantuan (jenis, nominal, nomor SK).
4. Pantau progres renovasi, dan tetapkan **Selesai** bila memenuhi aturan progres.

### Admin
1. Kelola akun: buat user, tetapkan role dan wilayah tugas, reset password.
2. Kelola hierarki wilayah.
3. Pantau **Audit Log** untuk menelusuri aktivitas.

---

## Aturan Branching & Alur Kerja Git

| Branch | Fungsi | Siapa yang boleh push |
|---|---|---|
| `main` | Versi stabil / demo | Tidak ada push langsung; hanya merge Pull Request (PR) dari `develop` di akhir sprint |
| `develop` | Integrasi seluruh fitur | Tidak ada push langsung; hanya merge Pull Request (PR) dari branch fitur |
| `feature/scrum-<nomor>-<slug>` | Satu per **tiket induk** Jira | Anggota yang mengerjakan sub-task tiket itu |

**Penamaan:** `feature/scrum-` + nomor Jira asli + slug huruf kecil dipisah tanda hubung, contoh `feature/scrum-10-login`. Nomor yang dipakai adalah nomor Jira, bukan urutan fase.

**Alur satu sub-task:**
1. Pindah ke branch tiket induknya: `git checkout feature/scrum-<n>-<slug>`.
2. Sinkronkan: `git fetch origin && git merge origin/develop`.
3. Kerjakan sub-task, commit kecil dan sering (lihat konvensi di bawah).
4. Sebelum push, tarik perubahan rekan di branch yang sama: `git pull origin feature/scrum-<n>-<slug>`.
5. Push, lalu buka Pull Request ke `develop` bila seluruh sub-task tiket induk selesai.

**Rilis:** di akhir sprint, buka Pull Request (PR) `develop` → `main`, lalu beri tag, mis. `git tag v0.1.0-sprint0 && git push origin v0.1.0-sprint0`.

**Dilarang:** `git push --force` ke `main`/`develop`, commit langsung ke keduanya, dan meng-commit `.env`, `vendor/`, `node_modules/`.

---

## Konvensi Commit

Format: `scrum-<nomor>: <kalimat perintah singkat>`. Nomor adalah nomor **sub-task** bila ada, atau nomor tiket induk bila tidak.

```
scrum-56: atur koneksi pgsql di .env.example
scrum-58: tulis README cara menjalankan proyek
scrum-10: tambah validasi form login
```

Satu commit memuat satu perubahan logis. Pesan ditulis dalam bahasa Indonesia atau Inggris, tetapi **konsisten** di seluruh tim.

---

## Pull Request & Review

- Tujuan PR: `develop` (kecuali rilis sprint ke `main`).
- Judul Pull Request (PR): `SCRUM-<nomor>: <judul tiket induk>`.
- Deskripsi minimal: tautan tiket Jira, ringkasan perubahan, cara menguji, dan tangkapan layar bila ada perubahan tampilan.
- Minimal **1 reviewer** selain pembuat Pull Request (PR); konflik diselesaikan oleh pembuat Pull Request (PR).
- Metode penggabungan: **Merge commit** (bukan Squash), agar kontribusi tiap anggota pada branch yang sama tetap tercatat.
- Setelah merge, status tiket di Jira dipindahkan sesuai Definition of Done.

---

## Definition of Done

Sebuah tiket dinyatakan selesai bila:

1. Seluruh sub-task selesai dan kriteria penerimaan di Jira terpenuhi.
2. Kode berjalan di macOS **dan** Windows (minimal dicoba satu anggota per OS untuk perubahan setup).
3. `php artisan migrate:fresh --seed` berjalan tanpa error.
4. Pengujian otomatis terkait lulus, dan tidak ada pengujian lama yang rusak.
5. Gaya kode sudah dirapikan (`./vendor/bin/pint`).
6. Perubahan struktur direktori/database sudah tercermin di README.
7. Pull Request (PR) sudah di-review dan di-merge ke `develop`.

---

## Standar Kode & Pengujian

- **Gaya kode:** PSR-12 melalui Laravel Pint (`./vendor/bin/pint`). Nama kelas `PascalCase`, metode `camelCase`, tabel dan kolom `snake_case`. Nama domain memakai istilah Indonesia sesuai ERD (mis. `RumahWarga`).
- **Lapisan:** controller tipis, validasi di `FormRequest`, logika bisnis di `app/Services`, otorisasi di `Policies`.
- **Pengujian:** `php artisan test`. Prioritas pada alur utama (login, input survei, review, perubahan status, penerima bantuan, progres) di SCRUM-40.
- **Migrasi:** jangan mengubah migrasi yang sudah ada di `develop`; buat migrasi baru.
- **Lintas OS:** gunakan `DIRECTORY_SEPARATOR`/helper path Laravel, jangan menulis path absolut khusus OS; perhatikan huruf besar-kecil nama berkas (Linux/produksi membedakannya, macOS/Windows umumnya tidak).

---

## Keamanan & Data Pribadi

Aplikasi memuat data pribadi warga, sehingga tunduk pada **UU No. 27 Tahun 2022 tentang Pelindungan Data Pribadi**.

- `.env`, kunci, dan kredensial tidak boleh masuk ke Git. Jika terlanjur ter-commit, segera ganti kuncinya dan beri tahu tim.
- NIK dan No. KK dienkripsi (AES-256-GCM) dan ditampilkan ter-masking; jangan dicatat ke log.
- Password di-hash (argon2id/bcrypt); tidak pernah disimpan atau dikirim sebagai teks biasa.
- Foto disimpan di luar folder publik dan disajikan lewat endpoint yang butuh autentikasi; unggahan divalidasi (JPG/PNG/WebP, maks. 5 MB).
- Gunakan **data dummy** untuk pengembangan, demo, dan tangkapan layar; jangan memakai data warga sungguhan.
- Penghapusan data memakai soft delete; `riwayat_status` dan `audit_log` bersifat append-only.

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
```

---

## Lisensi

[Lisensi ditetapkan tim. Proyek ini dikembangkan untuk keperluan akademik mata kuliah BBK3EAB3 - PROYEK PERANGKAT LUNAK Semester Ganjil 2026/2027.]
