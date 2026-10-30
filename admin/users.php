<?php
require '../config/config.php'; role('administrator');
if($_SERVER['REQUEST_METHOD']==='POST'){
 try{
  $a=$_POST['a']??'';
  if($a==='add'){
   $p=$_POST['password']?:'Perpus123!';
   $pdo->prepare("INSERT INTO users(name,username,password,role,status,class_name) VALUES(?,?,?,?,?,?)")->execute([trim($_POST['name']),trim($_POST['username']),password_hash($p,PASSWORD_DEFAULT),$_POST['role'],'aktif',trim($_POST['class_name'])]);
   setmsg("Akun berhasil dibuat. Password awal: $p");
  }elseif($a==='reset'){
   $p=$_POST['new_password']?:'Perpus123!';
   $pdo->prepare("UPDATE users SET password=? WHERE id=?")->execute([password_hash($p,PASSWORD_DEFAULT),(int)$_POST['id']]);
   setmsg("Password direset: $p");
  }elseif($a==='toggle'){
   $pdo->prepare("UPDATE users SET status=IF(status='aktif','nonaktif','aktif') WHERE id=?")->execute([(int)$_POST['id']]);
   setmsg('Status akun diubah.');
  }
  header('Location:users.php');exit;
 }catch(Throwable $e){setmsg($e->getCode()==23000?'Username/NIS sudah digunakan.':'Data akun tidak dapat disimpan.','danger');}
}
$u=$pdo->query("SELECT * FROM users ORDER BY FIELD(role,'administrator','pustakawan','anggota'),name")->fetchAll();
$active=count(array_filter($u,fn($x)=>$x['status']==='aktif'));$members=count(array_filter($u,fn($x)=>$x['role']==='anggota'));
?><!doctype html><html lang=id><head><meta charset=utf-8><meta name=viewport content="width=device-width,initial-scale=1"><title>Manajemen Akun — Perpustakaan SMPIT</title><link rel=stylesheet href="../public/style.css"><style>
.account-tools{display:flex;flex-wrap:wrap;gap:8px;margin-bottom:16px}.account-tools .btn{display:inline-flex;align-items:center;gap:5px}.summary{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:16px}.summary .card{padding:16px}.account-form{margin-bottom:16px}.form-title{display:flex;justify-content:space-between;align-items:center;gap:10px}.role-pill{display:inline-block;padding:4px 8px;border-radius:99px;background:#eef4ff;color:#2458a6;font-size:12px;font-weight:700}.status-pill{display:inline-block;padding:4px 8px;border-radius:99px;font-size:12px;font-weight:700}.aktif{background:#dcfce7;color:#166534}.nonaktif{background:#fee2e2;color:#991b1b}.row-actions{display:flex;flex-wrap:wrap;gap:5px}.row-actions form{margin:0}.searchbar{display:flex;gap:8px;margin-bottom:12px}.searchbar input{margin:0;flex:1}.member-check{width:auto;margin:0}.table-wrap{overflow:auto}@media(max-width:700px){.summary{grid-template-columns:1fr}.searchbar{display:block}.searchbar button{margin-top:6px}}
</style></head><body><div class="layout"><?php include '../public/nav.php';?><main class="main"><h1>👤 Manajemen Akun</h1><?php msg();?>
<div class="account-tools">
<a class="btn" href="scanner.php">📷 Scan Barcode Anggota</a>
<a class="btn" href="import_members.php">📥 Import Anggota dari Excel</a>
<a class="btn green" href="member_card.php?all=1" target="_blank">🖨️ Cetak Semua Kartu Aktif</a>
</div>
<div class="summary"><div class="card"><small>Total Akun</small><div class="num"><?=count($u)?></div></div><div class="card"><small>Total Anggota</small><div class="num"><?=$members?></div></div><div class="card"><small>Akun Aktif</small><div class="num"><?=$active?></div></div></div>
<div class="form account-form"><div class="form-title"><h2 style="margin:0">➕ Tambah Akun</h2><span class="role-pill">Anggota / Pustakawan / Administrator</span></div><p style="color:#64748b">Untuk anggota, Username/NIS digunakan untuk login dan barcode kartu anggota.</p>
<form method="post"><input type="hidden" name="a" value="add"><div class="grid"><div><label>Nama Lengkap</label><input name="name" required placeholder="Nama anggota"></div><div><label>Username / NIS</label><input name="username" required placeholder="Contoh: 24001"></div><div><label>Kelas</label><input name="class_name" placeholder="Contoh: VII A"></div><div><label>Role</label><select name="role"><option value="anggota">Anggota</option><option value="pustakawan">Pustakawan</option><option value="administrator">Administrator</option></select></div><div><label>Password</label><input name="password" placeholder="Kosong = Perpus123!"></div></div><button>+ Simpan Akun</button></form></div>
<div class="form"><div class="form-title"><h3 style="margin:0">📋 Daftar Akun</h3><span style="color:#64748b;font-size:13px"><?=count($u)?> akun</span></div>
<div class="searchbar"><input id="accountSearch" placeholder="Cari nama, NIS, kelas, atau role..." oninput="filterAccounts()"><a class="btn gray" href="member_card.php?all=1" target="_blank">🖨️ Semua Anggota Aktif</a></div>
<div class="table-wrap"><table class="table" id="accountTable"><tr><th>Nama</th><th>NIS/Username</th><th>Kelas</th><th>Role</th><th>Status</th><th>Aksi</th></tr>
<?php foreach($u as $x):?><tr data-search="<?=e(strtolower($x['name'].' '.$x['username'].' '.($x['class_name']??'').' '.$x['role'].' '.$x['status']))?>"><td><b><?=e($x['name'])?></b></td><td><?=e($x['username'])?></td><td><?=e($x['class_name']??'-')?></td><td><span class="role-pill"><?=e($x['role'])?></span></td><td><span class="status-pill <?=$x['status']?>"><?=e($x['status'])?></span></td><td><div class="row-actions"><?php if($x['role']==='anggota'):?><a class="btn gray" target="_blank" href="member_card.php?id=<?=$x['id']?>">🖨️ Kartu</a><?php endif;?><form method="post"><input type="hidden" name="a" value="reset"><input type="hidden" name="id" value="<?=$x['id']?>"><input type="hidden" name="new_password" value="Perpus123!"><button>🔑 Reset</button></form><form method="post"><input type="hidden" name="a" value="toggle"><input type="hidden" name="id" value="<?=$x['id']?>"><button class="btn <?=$x['status']==='aktif'?'red':'green'?>"><?=($x['status']==='aktif'?'Nonaktifkan':'Aktifkan')?></button></form><?php if($x['role']==='anggota'):?><a class="btn" href="../reports/member_activity.php?id=<?=$x['id']?>">📊 Aktivitas</a><?php endif;?></div></td></tr><?php endforeach?>
<?php if(!$u):?><tr><td colspan="6">Belum ada akun.</td></tr><?php endif?></table></div></div></main></div>
<script>function filterAccounts(){const q=document.getElementById('accountSearch').value.toLowerCase().trim();document.querySelectorAll('#accountTable tr[data-search]').forEach(r=>r.style.display=(!q||r.dataset.search.includes(q))?'':'none')}</script>
</body></html>