<?php
require __DIR__.'/../config/config.php'; role(['administrator','pustakawan']);
try{
  $pdo->exec("CREATE TABLE IF NOT EXISTS categories(id INT AUTO_INCREMENT PRIMARY KEY,name VARCHAR(50) NOT NULL UNIQUE) ENGINE=InnoDB");
  foreach(['Fiksi','Nonfiksi'] as $name){
    $st=$pdo->prepare("INSERT IGNORE INTO categories(name) VALUES(?)");$st->execute([$name]);
  }
  $pdo->exec("DELETE FROM categories WHERE name NOT IN ('Fiksi','Nonfiksi')");
  echo '<h2>Kategori buku sudah disiapkan.</h2><ul><li>Fiksi</li><li>Nonfiksi</li></ul><p><a href="books.php">Kembali ke Data Buku</a></p>';
}catch(Throwable $e){http_response_code(500);echo '<pre>'.htmlspecialchars($e->getMessage()).'</pre>';}
