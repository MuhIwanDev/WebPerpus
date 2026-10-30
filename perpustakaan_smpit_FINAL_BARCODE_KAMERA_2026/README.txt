PERPUSTAKAAN SMPIT - FINAL UPDATE
PHP + MySQL + XAMPP

FITUR UTAMA
- Dashboard Perpustakaan SMPIT dengan statistik buku, eksemplar, ketersediaan, peminjaman, anggota, keterlambatan.
- Kunjungan Hari Ini dan Kunjungan Bulan Ini yang dapat dibuka langsung dari dashboard.
- Peminjaman Segera Jatuh Tempo menampilkan transaksi yang jatuh tempo dalam 7 hari ke depan.
- Anggota Paling Aktif dengan detail kunjungan, peminjaman, pengembalian, dan total aktivitas.
- Data Buku: kode/inventaris, ISBN, judul, penulis, penerbit, tahun terbit, kategori, jumlah eksemplar.
- Eksemplar: barcode, lokasi/rak, kondisi (baik/rusak ringan/rusak berat), status (tersedia/dipinjam/rusak/hilang).
- Ubah barcode eksemplar dengan alasan dan riwayat perubahan. Barcode baru harus unik.
- Cetak barcode eksemplar.
- Peminjaman/pengembalian cepat dengan scanner barcode USB.
- Login anggota terpisah: hanya dashboard anggota, search buku, pinjam buku, kembalikan buku, dan kunjungan otomatis saat login.
- Manajemen akun: tambah akun, reset password, aktif/nonaktif, import anggota dari Excel/CSV, cetak kartu anggota dengan barcode.
- Kategori Buku dan Rak/Lokasi.
- Riwayat transaksi dan keterlambatan.
- Laporan bulanan dan export Excel.

CARA INSTALASI
1. Ekstrak folder perpustakaan_smpit_v2 ke C:\xampp\htdocs\
2. Jalankan Apache dan MySQL dari XAMPP.
3. Buka http://localhost/perpustakaan_smpit_v2/install.php
4. Setelah instalasi berhasil, buka http://localhost/perpustakaan_smpit_v2/public/login.php
5. Login administrator: admin / admin123
6. Login pustakawan: pustakawan / pustaka123
7. Login anggota demo: 2026001 / anggota123
8. Setelah berhasil, hapus atau rename install.php untuk keamanan.

CATATAN
- Scanner barcode USB bekerja sebagai input keyboard. Arahkan kursor ke kolom scan lalu scan barcode.
- Kartu anggota memakai barcode CODE128 berdasarkan username/NIS.
- Cetak barcode eksemplar dan kartu anggota menggunakan JsBarcode dari CDN sehingga halaman cetak membutuhkan internet saat library pertama kali dimuat.
- Pengaturan jatuh tempo dapat diubah melalui menu Pengaturan Jatuh Tempo.
