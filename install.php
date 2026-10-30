<?php
$host='127.0.0.1'; $user='root'; $pass='';
try {
$pdo=new PDO("mysql:host=$host;charset=utf8mb4",$user,$pass,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
$pdo->exec("CREATE DATABASE IF NOT EXISTS perpustakaan_smpit CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");$pdo->exec("USE perpustakaan_smpit");
$pdo->exec("CREATE TABLE IF NOT EXISTS users(id INT AUTO_INCREMENT PRIMARY KEY,name VARCHAR(150) NOT NULL,username VARCHAR(80) UNIQUE NOT NULL,password VARCHAR(255) NOT NULL,role ENUM('administrator','pustakawan','anggota') DEFAULT 'anggota',status ENUM('aktif','nonaktif') DEFAULT 'aktif',class_name VARCHAR(50),created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB");
$pdo->exec("CREATE TABLE IF NOT EXISTS categories(id INT AUTO_INCREMENT PRIMARY KEY,name VARCHAR(100) NOT NULL UNIQUE,description VARCHAR(255)) ENGINE=InnoDB");
$pdo->exec("CREATE TABLE IF NOT EXISTS locations(id INT AUTO_INCREMENT PRIMARY KEY,name VARCHAR(100) NOT NULL UNIQUE,description VARCHAR(255)) ENGINE=InnoDB");
$pdo->exec("CREATE TABLE IF NOT EXISTS books(id INT AUTO_INCREMENT PRIMARY KEY,code VARCHAR(50) UNIQUE NOT NULL,title VARCHAR(255) NOT NULL,author VARCHAR(150),publisher VARCHAR(150),created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB");
$bookCols=$pdo->query('SHOW COLUMNS FROM books')->fetchAll(PDO::FETCH_COLUMN);
if(!in_array('isbn',$bookCols,true))$pdo->exec("ALTER TABLE books ADD isbn VARCHAR(30) NULL AFTER code");
if(!in_array('publisher',$bookCols,true))$pdo->exec("ALTER TABLE books ADD publisher VARCHAR(150) NULL AFTER author");
if(!in_array('publication_year',$bookCols,true))$pdo->exec("ALTER TABLE books ADD publication_year SMALLINT NULL AFTER publisher");
if(!in_array('category_id',$bookCols,true))$pdo->exec("ALTER TABLE books ADD category_id INT NULL AFTER publication_year");
$pdo->exec("CREATE TABLE IF NOT EXISTS copies(id INT AUTO_INCREMENT PRIMARY KEY,book_id INT NOT NULL,code VARCHAR(80) UNIQUE NOT NULL,status ENUM('tersedia','dipinjam','rusak','hilang') DEFAULT 'tersedia',FOREIGN KEY(book_id) REFERENCES books(id) ON DELETE CASCADE) ENGINE=InnoDB");
$copyCols=$pdo->query('SHOW COLUMNS FROM copies')->fetchAll(PDO::FETCH_COLUMN);
if(!in_array('location_id',$copyCols,true))$pdo->exec("ALTER TABLE copies ADD location_id INT NULL AFTER code");
if(!in_array('condition_status',$copyCols,true))$pdo->exec("ALTER TABLE copies ADD condition_status ENUM('baik','rusak ringan','rusak berat') NOT NULL DEFAULT 'baik' AFTER location_id");
$pdo->exec("CREATE TABLE IF NOT EXISTS barcode_history(id INT AUTO_INCREMENT PRIMARY KEY,copy_id INT NOT NULL,old_code VARCHAR(80) NOT NULL,new_code VARCHAR(80) NOT NULL,reason VARCHAR(255) NOT NULL,changed_by INT NULL,changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,INDEX(copy_id)) ENGINE=InnoDB");
$pdo->exec("CREATE TABLE IF NOT EXISTS loans(id INT AUTO_INCREMENT PRIMARY KEY,user_id INT NOT NULL,copy_id INT NOT NULL,loan_date DATE NOT NULL,due_date DATE NOT NULL,returned_at DATETIME NULL,late_days INT DEFAULT 0,fine DECIMAL(12,2) DEFAULT 0,FOREIGN KEY(user_id) REFERENCES users(id),FOREIGN KEY(copy_id) REFERENCES copies(id)) ENGINE=InnoDB");
$pdo->exec("CREATE TABLE IF NOT EXISTS visits(id INT AUTO_INCREMENT PRIMARY KEY,user_id INT NOT NULL,visit_date DATE NOT NULL,visit_time TIME NOT NULL,FOREIGN KEY(user_id) REFERENCES users(id)) ENGINE=InnoDB");
$pdo->exec("CREATE TABLE IF NOT EXISTS login_logs(id INT AUTO_INCREMENT PRIMARY KEY,user_id INT NOT NULL,login_at DATETIME NOT NULL,FOREIGN KEY(user_id) REFERENCES users(id)) ENGINE=InnoDB");
$pdo->exec("CREATE TABLE IF NOT EXISTS settings(`key` VARCHAR(50) PRIMARY KEY,`value` VARCHAR(100) NOT NULL) ENGINE=InnoDB");
$st=$pdo->prepare("INSERT INTO settings(`key`,`value`) VALUES(?,?) ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)");foreach([['loan_days','7'],['max_loans','2'],['fine_per_day','500']] as $x)$st->execute($x);
$check=$pdo->prepare('SELECT id FROM users WHERE username=?');$ins=$pdo->prepare('INSERT INTO users(name,username,password,role,status,class_name) VALUES(?,?,?,?,?,?)');
foreach([['Administrator','admin','admin123','administrator','aktif',null],['Pustakawan','pustakawan','pustaka123','pustakawan','aktif',null],['Anggota Demo','2026001','anggota123','anggota','aktif','VII A']] as $a){$check->execute([$a[1]]);if(!$check->fetch())$ins->execute([$a[0],$a[1],password_hash($a[2],PASSWORD_DEFAULT),$a[3],$a[4],$a[5]]);}
?><!doctype html><meta charset=utf-8><title>Instalasi Berhasil</title><style>body{font-family:Arial;background:#f4f7fb;max-width:760px;margin:40px auto;padding:20px}.box{background:white;padding:30px;border-radius:18px;box-shadow:0 8px 30px #1232}.btn{display:inline-block;background:#1260d1;color:white;padding:11px 16px;border-radius:8px;text-decoration:none}</style><div class=box><h2>✅ Instalasi / Perbaikan Berhasil</h2><p>Database siap. Struktur data buku, eksemplar, lokasi, kondisi, status, barcode history, kunjungan, transaksi, dan akun sudah disiapkan.</p><p><b>Administrator:</b> admin / admin123<br><b>Pustakawan:</b> pustakawan / pustaka123<br><b>Anggota:</b> 2026001 / anggota123</p><a class=btn href=public/login.php>Buka Login</a><p><b>Penting:</b> setelah selesai, hapus/rename file <code>install.php</code>.</p></div><?php
} catch(Throwable $e){http_response_code(500);echo '<h2>Instalasi gagal</h2><pre>'.htmlspecialchars($e->getMessage()).'</pre>';}
