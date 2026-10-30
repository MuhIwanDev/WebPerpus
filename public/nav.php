<?php
require_once __DIR__ . '/../config/config.php';
$base = $BASE_URL ?? '';
$role = $_SESSION['u']['role'] ?? '';
?>
<aside class="side">
  <div class="logo-area"><img src="<?=$base?>/assets/logo-perpustakaan.png" alt="Perpustakaan SMPIT"></div>
  <div class="side-title">Perpustakaan SMPIT</div>
  <div class="side-sub">Sistem Informasi Perpustakaan</div>
  <a class="<?=basename($_SERVER['PHP_SELF'])==='index.php'?'active':''?>" href="<?=$base?>/public/index.php">📊 Dashboard</a>

  <div class="nav-section">PERPUSTAKAAN</div>
  <a href="<?=$base?>/public/search.php">🔎 Cari Buku</a>
  <?php if($role!=='anggota'): ?>
  <a href="<?=$base?>/admin/books.php">📚 Data Buku</a>
  <a href="<?=$base?>/admin/copies.php">🏷️ Eksemplar Buku</a>
  <a href="<?=$base?>/admin/categories.php">◉ Kategori Buku</a>
  <a href="<?=$base?>/admin/locations.php">📍 Rak / Lokasi</a>
  <a href="<?=$base?>/admin/barcode_labels.php">▥ Cetak Barcode</a>
  <a href="<?=$base?>/admin/scanner.php">📷 Scan Barcode</a>

  <div class="nav-section">TRANSAKSI</div>
  <a href="<?=$base?>/admin/loans.php">🔄 Peminjaman</a>
  <a href="<?=$base?>/admin/returns.php">📥 Pengembalian</a>
  <a href="<?=$base?>/admin/history.php">☷ Riwayat Transaksi</a>
  <a href="<?=$base?>/admin/overdue.php">◷ Keterlambatan</a>
  <a href="<?=$base?>/admin/due_soon.php">⏰ Segera Jatuh Tempo</a>

  <div class="nav-section">ANGGOTA</div>
  <a href="<?=$base?>/admin/users.php">👥 Manajemen Akun</a>
  <a href="<?=$base?>/admin/visits.php">🚶 Kunjungan Perpustakaan</a>

  <div class="nav-section">INVENTARIS</div>
  <a href="<?=$base?>/admin/inventory.php">📦 Data Inventaris</a>

  <div class="nav-section">LAPORAN</div>
  <a href="<?=$base?>/reports/monthly.php">📊 Laporan Bulanan</a>
  <a href="<?=$base?>/reports/members.php">🏆 Anggota Paling Aktif</a>

  <div class="nav-section">PENGGUNA</div>
  <a href="<?=$base?>/admin/users.php">👤 Administrator & Pustakawan</a>

  <div class="nav-section">PENGATURAN</div>
  <a href="<?=$base?>/admin/settings.php">⚙️ Pengaturan Perpustakaan</a>
  <?php endif; ?>
  <?php if($role==='anggota'): ?>
  <div class="nav-section">AKTIVITAS SAYA</div>
  <a href="<?=$base?>/member/dashboard.php">👤 Dashboard Anggota</a>
  <a href="<?=$base?>/member/search.php">🔎 Cari & Pinjam Buku</a>
  <a href="<?=$base?>/member/visits.php">🚶 Kunjungan Saya</a>
  <?php endif; ?>
  <a href="<?=$base?>/public/logout.php">🚪 Keluar</a>
</aside>
