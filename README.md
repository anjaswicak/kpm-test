# Mini Project IT Support - Aplikasi Web Kompetisi/Ujian

Dokumentasi ini berisi tata cara konfigurasi dan instalasi aplikasi di local server sampai siap digunakan.

## 1. Kebutuhan Sistem

- PHP 8.3+
- Composer 2+
- Node.js 18+ dan npm
- MySQL 8+ (atau MariaDB setara)
- Git

## 2. Clone Project

```bash
git clone <url-repository-anda>
cd mini-project-it-support
```

## 3. Install Dependency

Install dependency backend:

```bash
composer install
```

Install dependency frontend:

```bash
npm install
```

## 4. Konfigurasi Environment

Copy file env:

```bash
cp .env.example .env
```

Jika memakai Windows PowerShell:

```powershell
Copy-Item .env.example .env
```

Lalu ubah konfigurasi penting di `.env`:

```env
APP_NAME="Mini Project IT Support"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000
APP_TIMEZONE=Asia/Jakarta

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mini-project-it-support
DB_USERNAME=root
DB_PASSWORD=
```

Catatan:
- Timezone aplikasi sudah membaca `APP_TIMEZONE` dari env dan default ke `Asia/Jakarta`.
- Pastikan database `mini-project-it-support` sudah dibuat di MySQL sebelum migrate.

## 5. Generate App Key

```bash
php artisan key:generate
```

## 6. Migrasi Database

```bash
php artisan migrate
```

Setelah migrate, aplikasi otomatis membuat akun admin default:
- Email: `kpmtest@kpm.com`
- Username/Nama: `kpmtest`
- Password: `kpmtest123`

## 7. Link Storage (Wajib untuk Gambar Soal)

Supaya upload gambar soal bisa tampil di browser:

```bash
php artisan storage:link
```

## 8. Menjalankan Aplikasi

Jalankan backend Laravel:

```bash
php artisan serve
```

Jalankan frontend Vite (terminal terpisah):

```bash
npm run dev
```

Buka aplikasi di browser:
- `http://127.0.0.1:8000`

## 9. Fitur Utama Aplikasi

### Guest
- Landing page
- Login dan Register

### Admin
- Manajemen ujian (buat, edit, publish, unpublish, close)
- Kelola soal ujian (opsi jawaban, poin, upload gambar)
- Tambah waktu ujian per peserta
- Melihat daftar ujian dan statusnya

### Peserta/User
- Daftar ujian
- Mengerjakan ujian dalam mode fullscreen
- Navigasi soal tanpa refresh (nomor soal, prev/next)
- Autosave jawaban berkala
- Countdown waktu ujian
- Realtime update waktu ketika admin menambahkan extra time
- Review hasil setelah submit

### Proteksi Saat Ujian
- Monitoring pelanggaran (keluar tab, blur, keluar fullscreen)
- Auto submit jika waktu habis
- Auto submit jika batas pelanggaran tercapai

## 10. Troubleshooting Cepat

### A. Gambar soal tidak tampil
Jalankan ulang:

```bash
php artisan storage:link
```

Pastikan file ada di folder `storage/app/public/questions`.

### B. Styling tidak ter-load
Pastikan Vite aktif:

```bash
npm run dev
```

### C. Gagal koneksi database
- Cek kredensial DB di `.env`
- Cek service MySQL aktif
- Jalankan ulang migrate setelah konfigurasi benar

## 11. Command Ringkas (Setup Cepat)

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan storage:link
php artisan serve
```

Di terminal lain:

```bash
npm run dev
```
