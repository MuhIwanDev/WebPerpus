<?php
require '../config/config.php';
if(isset($_SESSION['u'])){header('Location:'.($_SESSION['u']['role']==='anggota'?'../member/dashboard.php':'index.php'));exit;}
$err='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 $s=$pdo->prepare("SELECT * FROM users WHERE username=? AND status='aktif'");$s->execute([trim($_POST['username']??'')]);$u=$s->fetch();
 if($u&&password_verify($_POST['password']??'',$u['password'])){
   $_SESSION['u']=['id'=>$u['id'],'name'=>$u['name'],'role'=>$u['role'],'username'=>$u['username']];
   if($u['role']==='anggota')$pdo->prepare('INSERT INTO visits(user_id,visit_date,visit_time) VALUES(?,CURDATE(),CURTIME())')->execute([$u['id']]);
   header('Location:'.($u['role']==='anggota'?'../member/dashboard.php':'index.php'));exit;
 }
 $err='Username, password, atau status akun tidak sesuai.';
}
?><!doctype html><html lang=id><head><meta charset=utf-8><meta name=viewport content="width=device-width,initial-scale=1"><title>Login - Perpustakaan SMPIT</title><link rel=stylesheet href=style.css></head><body class=login><div class=box><img src="<?=$BASE_URL?>/assets/logo-perpustakaan.png" alt="Perpustakaan SMPIT" class=login-logo><h2>Perpustakaan SMPIT</h2><p style="color:#64748b">Sistem Informasi Perpustakaan</p><?php if($err):?><div class="alert danger"><?=e($err)?></div><?php endif?><form method=post><label>Username / NIS</label><input name=username required autofocus><label>Password</label><input type=password name=password required><button>Masuk</button></form></div></body></html>
