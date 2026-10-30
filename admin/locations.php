<?php
require '../config/config.php'; role(['administrator','pustakawan']);
$pdo->exec("CREATE TABLE IF NOT EXISTS locations(id INT AUTO_INCREMENT PRIMARY KEY,name VARCHAR(100) NOT NULL UNIQUE,description VARCHAR(255) NULL) ENGINE=InnoDB");
if($_SERVER['REQUEST_METHOD']==='POST'){
 try{
  $a=$_POST['a']??'add';$id=(int)($_POST['id']??0);$name=trim($_POST['name']??'');$desc=trim($_POST['description']??'');
  if($name==='')throw new Exception('Nama wajib diisi.');
  if($a==='edit'&&$id){$pdo->prepare("UPDATE locations SET name=?,description=? WHERE id=?")->execute([$name,$desc,$id]);setmsg('Rak/lokasi berhasil diperbarui.');}
  else{$pdo->prepare("INSERT INTO locations(name,description) VALUES(?,?)")->execute([$name,$desc]);setmsg('Rak/lokasi berhasil ditambahkan.');}
  header('Location:locations.php');exit;
 }catch(Throwable $e){setmsg($e->getCode()==23000?'Rak/lokasi sudah ada.':'Data tidak dapat disimpan.','danger');}
}
if(isset($_GET['delete'])){try{$pdo->prepare("DELETE FROM locations WHERE id=?")->execute([(int)$_GET['delete']]);setmsg('Rak/lokasi dihapus.');}catch(Throwable $e){setmsg('Data tidak dapat dihapus karena masih digunakan.','danger');}header('Location:locations.php');exit;}
$edit=null;if(isset($_GET['edit'])){$s=$pdo->prepare("SELECT * FROM locations WHERE id=?");$s->execute([(int)$_GET['edit']]);$edit=$s->fetch();}
$r=$pdo->query("SELECT * FROM locations ORDER BY name")->fetchAll();
?><!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Rak / Lokasi — Perpustakaan SMPIT</title><link rel="stylesheet" href="../public/style.css"></head><body><div class="layout"><?php include '../public/nav.php';?><main class="main"><h1>📍 Rak / Lokasi</h1><?php msg()?>
<div class="form"><h3><?= $edit ? '✏️ Edit' : '➕ Tambah' ?> Nama Rak/Lokasi</h3><form method="post"><input type="hidden" name="a" value="<?= $edit?'edit':'add' ?>"><input type="hidden" name="id" value="<?= $edit['id']??0 ?>"><div class="grid"><div><label>Nama Rak/Lokasi</label><input name="name" required value="<?=e($edit['name']??'')?>" placeholder="Contoh: Rak A1"></div><div><label>Deskripsi</label><input name="description" value="<?=e($edit['description']??'')?>" placeholder="Keterangan"></div></div><button><?= $edit?'💾 Simpan Perubahan':'+ Tambah' ?></button><?php if($edit):?><a class="btn gray" href="locations.php">Batal</a><?php endif;?></form></div>
<br><div class="card"><h3>📋 Daftar Rak / Lokasi</h3><div class="table-wrap"><table class="table"><tr><th>No</th><th>Nama</th><th>Deskripsi</th><th>Aksi</th></tr><?php foreach($r as $i=>$x):?><tr><td><?=$i+1?></td><td><b><?=e($x['name'])?></b></td><td><?=e($x['description']?:'-')?></td><td><a class="btn" href="?edit=<?=$x['id']?>">✏️ Edit</a> <a class="btn red" href="?delete=<?=$x['id']?>" onclick="return confirm('Hapus data ini?')">Hapus</a></td></tr><?php endforeach?><?php if(!$r):?><tr><td colspan="4">Belum ada data.</td></tr><?php endif?></table></div></div>
</main></div></body></html>