# Sistem Pengontrolan Barang (Inventory Control System)

Sistem manajemen inventori sederhana yang dibangun dengan PHP dan MySQL.

## Fitur Utama

### 1. **Autentikasi**
- Login sistem (tanpa registrasi)
- Manajemen session
- Role-based access (Admin/User)

### 2. **Dashboard**
- Grafik stok berdasarkan kategori
- Kombinasi chart (bar + line) untuk stok aktual, minimum, dan maksimum
- Statistik ringkasan
- Alert stok rendah

### 3. **Data Stock**
- **Raw Material** - Bahan baku
- **Barang Setengah Jadi** - Semi-finished goods
- **Finish Good** - Produk jadi
- **Consumable** - Barang habis pakai
- CRUD operations untuk semua kategori
- Status monitoring (Normal/Rendah/Tinggi)

### 4. **Planning Produksi**
- Perencanaan produksi harian
- Kalender view untuk 1 bulan penuh
- Status tracking (Planned/In Progress/Completed)
- CRUD operations untuk rencana produksi

### 5. **Production Result**
- Input hasil produksi berdasarkan planning
- Perbandingan target vs aktual
- Persentase pencapaian
- Catatan hasil produksi

### 6. **User Management** (Admin only)
- Tambah/edit/hapus user
- Role assignment
- Permission management
- User statistics

### 7. **Pengaturan** (Admin only)
- Konfigurasi judul website
- Upload logo
- Pengaturan bahasa
- Database backup
- System information

### 8. **UI/UX Features**
- Sidebar yang dapat diminimize
- Responsive design
- Modern Bootstrap 5 interface
- Font Awesome icons
- Chart.js untuk visualisasi data

## Instalasi

### Persyaratan Sistem
- PHP 7.4 atau lebih tinggi
- MySQL 5.7 atau lebih tinggi
- Web server (Apache/Nginx)

### Langkah Instalasi

1. **Clone atau download project**
   ```bash
   git clone [repository-url]
   cd inventory-system
   ```

2. **Setup Database**
   - Buat database MySQL baru
   - Import file `database/schema.sql`
   ```sql
   CREATE DATABASE inventory_db;
   USE inventory_db;
   SOURCE database/schema.sql;
   ```

3. **Konfigurasi Database**
   - Edit file `config/database.php`
   - Sesuaikan kredensial database:
   ```php
   $host = 'localhost';
   $dbname = 'inventory_db';
   $username = 'your_username';
   $password = 'your_password';
   ```

4. **Setup Web Server**
   - Arahkan document root ke folder project
   - Pastikan PHP dan MySQL service berjalan

5. **Akses Aplikasi**
   - Buka browser dan akses: `http://localhost/inventory-system`
   - Login dengan kredensial default:
     - Username: `admin`
     - Password: `admin123`

## Struktur Project

```
inventory-system/
├── config/
│   └── database.php          # Konfigurasi database
├── includes/
│   ├── functions.php         # Helper functions
│   ├── header.php           # Header template
│   └── footer.php           # Footer template
├── database/
│   └── schema.sql           # Database schema
├── assets/
│   ├── css/                 # Custom CSS (jika ada)
│   ├── js/                  # Custom JavaScript (jika ada)
│   └── images/              # Upload images
├── backups/                 # Database backups
├── login.php                # Halaman login
├── dashboard.php            # Dashboard utama
├── stock.php                # Manajemen stok
├── production-planning.php  # Planning produksi
├── production-result.php    # Hasil produksi
├── users.php                # User management
├── settings.php             # Pengaturan sistem
├── logout.php               # Logout handler
├── index.php                # Entry point
└── README.md                # Dokumentasi
```

## Default Data

### User Default
- **Username:** admin
- **Password:** admin123
- **Role:** Admin

### Kategori Default
1. Raw Materials
2. Semi Finished
3. Finished Goods
4. Consumables

### Sample Items
- Steel Plate (Raw Material)
- Plastic Pellets (Raw Material)
- Semi Product A (Semi Finished)
- Final Product X (Finished Good)
- Cleaning Supplies (Consumable)

## Penggunaan

### Login
1. Akses halaman login
2. Masukkan username dan password
3. Sistem akan redirect ke dashboard

### Dashboard
- Lihat overview stok
- Monitor item dengan stok rendah
- Analisis grafik stok per kategori

### Manajemen Stok
1. Pilih kategori dari menu Data Stock
2. Tambah item baru dengan tombol "Tambah Item"
3. Edit/hapus item existing
4. Monitor status stok (Normal/Rendah/Tinggi)

### Planning Produksi
1. Akses menu "Planning Produksi"
2. Pilih bulan/tahun yang diinginkan
3. Tambah rencana produksi dengan tanggal dan item
4. Lihat kalender view untuk overview bulanan

### Input Hasil Produksi
1. Akses menu "Production Result"
2. Pilih rencana produksi yang sudah ada
3. Input hasil aktual dan catatan
4. Sistem akan menghitung persentase pencapaian

### User Management (Admin)
1. Akses menu "User Management"
2. Tambah user baru dengan role dan permission
3. Edit user existing
4. Hapus user (kecuali user yang sedang login)

### Pengaturan (Admin)
1. Akses menu "Pengaturan"
2. Update judul website dan logo
3. Konfigurasi backup database
4. Lihat informasi sistem

## Keamanan

- Password di-hash menggunakan PHP `password_hash()`
- Session management untuk autentikasi
- Role-based access control
- Input validation dan sanitization
- SQL prepared statements untuk mencegah injection

## Teknologi yang Digunakan

- **Backend:** PHP 7.4+
- **Database:** MySQL 5.7+
- **Frontend:** HTML5, CSS3, JavaScript
- **Framework CSS:** Bootstrap 5
- **Icons:** Font Awesome 6
- **Charts:** Chart.js
- **Server:** Apache/Nginx

## Kontribusi

Untuk berkontribusi pada project ini:
1. Fork repository
2. Buat branch feature baru
3. Commit perubahan
4. Push ke branch
5. Buat Pull Request

## Lisensi

Project ini menggunakan lisensi MIT. Silakan gunakan dan modifikasi sesuai kebutuhan.

## Support

Jika mengalami masalah atau membutuhkan bantuan, silakan buat issue di repository atau hubungi developer.

---

**Catatan:** Sistem ini dibuat untuk keperluan pembelajaran dan penggunaan internal. Untuk penggunaan production, pastikan untuk melakukan security audit dan optimisasi performa yang diperlukan.
