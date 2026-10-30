<?php
require __DIR__.'/config/config.php';
role(['administrator']);
try{
  $cols=$pdo->query("SHOW COLUMNS FROM users")->fetchAll(PDO::FETCH_COLUMN);
  if(!in_array('barcode',$cols)){
    $pdo->exec("ALTER TABLE users ADD barcode VARCHAR(80) NULL UNIQUE AFTER username");
  }
  $rows=$pdo->query("SELECT id FROM users WHERE role='anggota' AND (barcode IS NULL OR barcode='')")->fetchAll(PDO::FETCH_COLUMN);
  $st=$pdo->prepare("UPDATE users SET barcode=? WHERE id=?");
  foreach($rows as $id){$st->execute(['AG-'.str_pad((string)$id,6,'0',STR_PAD_LEFT),$id]);}
  echo '<h2>Barcode anggota berhasil diperbarui.</h2><p><a href="users.php">Kembali ke Manajemen Akun</a></p>';
}catch(Throwable $e){http_response_code(500);echo '<h2>Gagal memperbarui barcode anggota</h2><pre>'.htmlspecialchars($e->getMessage()).'</pre>';}
