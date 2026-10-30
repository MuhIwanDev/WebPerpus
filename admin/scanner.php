<?php
require '../config/config.php'; role(['administrator','pustakawan']);
$code=trim($_GET['code']??''); $copy=null; $member=null;
if($code){
  try{
    $q=$pdo->prepare("SELECT c.*,b.title,b.isbn,b.code book_code,l.name location_name
                      FROM copies c JOIN books b ON b.id=c.book_id
                      LEFT JOIN locations l ON l.id=c.location_id WHERE c.code=? LIMIT 1");
    $q->execute([$code]); $copy=$q->fetch();
    if(!$copy){
      $q=$pdo->prepare("SELECT id,name,username,class_name,barcode FROM users WHERE (username=? OR barcode=?) AND role='anggota' LIMIT 1");
      $q->execute([$code,$code]); $member=$q->fetch();
    }
  }catch(Throwable $e){}
}
?><!doctype html>
<html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Scan Barcode — Perpustakaan SMPIT</title>
<link rel="stylesheet" href="../public/style.css">
<script src="https://unpkg.com/html5-qrcode" defer></script>
<style>
.scan-wrap{max-width:920px;margin:auto}.scan-grid{display:grid;grid-template-columns:1fr 1fr;gap:18px}
.scan-card{background:#fff;border:1px solid #e5eaf1;border-radius:16px;padding:20px;box-shadow:0 5px 20px rgba(10,40,80,.07)}
#reader{width:100%;max-width:520px;margin:auto}.status{padding:10px 12px;border-radius:10px;background:#eef5ff;color:#1455a0;margin:12px 0}
.result{margin-top:15px;padding:18px;border-radius:12px;background:#f4f9ff;border:1px solid #cfe2ff}
.big{font-size:20px;font-weight:800}.btnrow{display:flex;gap:8px;flex-wrap:wrap;margin-top:12px}
.btn{display:inline-block;padding:9px 13px;border-radius:8px;background:#1263d6;color:#fff;text-decoration:none;border:0;cursor:pointer}
.btn.gray{background:#64748b}
input.scan-input{width:100%;box-sizing:border-box;padding:12px;border:1px solid #ccd7e5;border-radius:9px;font-size:16px}
@media(max-width:800px){.scan-grid{grid-template-columns:1fr}}
</style></head>
<body><div class="layout"><?php include '../public/nav.php';?>
<main class="main"><div class="scan-wrap"><h1>📷 Scan Barcode / QR</h1>
<div class="scan-grid">
<section class="scan-card">
<h2>Scan dengan Kamera</h2>
<p class="status" id="scanStatus">Meminta izin kamera…</p>
<div id="reader"></div>
<div class="btnrow"><button class="btn" type="button" onclick="startScanner()">▶ Mulai Kamera</button><button class="btn gray" type="button" onclick="stopScanner()">■ Hentikan</button></div>
</section>
<section class="scan-card">
<h2>Scan dengan Barcode Scanner</h2>
<p>Jika memakai scanner USB, klik kotak lalu scan. Scanner akan mengisi kode seperti keyboard.</p>
<form method="get"><input class="scan-input" name="code" value="<?=e($code)?>" autofocus placeholder="BK-0001-01 / kode anggota"><div class="btnrow"><button class="btn">🔎 Cari Kode</button></div></form>
<?php if($copy):?><div class="result"><div class="big">📚 <?=e($copy['title'])?></div>
<p>Barcode: <b><?=e($copy['code'])?></b><br>ISBN: <?=e($copy['isbn']??'-')?><br>Status: <b><?=e($copy['status'])?></b><br>Kondisi: <?=e($copy['condition_status']??'-')?><br>Rak: <?=e($copy['location_name']??'-')?></p>
<div class="btnrow"><a class="btn" href="loans.php?copy_code=<?=urlencode($copy['code'])?>">🔖 Pinjam</a><a class="btn gray" href="returns.php?copy_code=<?=urlencode($copy['code'])?>">↩️ Kembalikan</a></div></div>
<?php elseif($member):?><div class="result"><div class="big">👤 <?=e($member['name'])?></div>
<p>Kode anggota: <b><?=e($member['barcode']??$member['username'])?></b><br>Username/NIS: <?=e($member['username'])?><br>Kelas: <?=e($member['class_name']??'-')?></p>
<div class="btnrow"><a class="btn" href="loans.php?member_code=<?=urlencode($member['username'])?>">🔖 Peminjaman</a></div></div>
<?php elseif($code):?><div class="result">Kode tidak ditemukan.</div><?php endif;?>
</section></div></div></main></div>
<script>
let scanner=null;
function setStatus(t){const e=document.getElementById('scanStatus'); if(e)e.textContent=t;}
async function startScanner(){
  if(!window.Html5Qrcode){setStatus('Library kamera belum siap. Tunggu sebentar lalu tekan Mulai Kamera.');return;}
  try{
    if(scanner){await scanner.stop().catch(()=>{});scanner.clear();}
    scanner=new Html5Qrcode("reader");
    setStatus('Memulai kamera… Izinkan akses kamera jika browser meminta.');
    await scanner.start({facingMode:"environment"},{fps:10,qrbox:{width:260,height:160}},
      (decoded)=>{setStatus('Kode terbaca: '+decoded); window.location.href='scanner.php?code='+encodeURIComponent(decoded);},
      ()=>{}
    );
    setStatus('Kamera aktif. Arahkan barcode/QR ke kotak pemindai.');
  }catch(e){
    setStatus('Kamera tidak dapat dibuka. Pastikan memakai HTTPS atau localhost, kamera tidak sedang dipakai aplikasi lain, dan izin kamera sudah diberikan.');
  }
}
async function stopScanner(){if(scanner){try{await scanner.stop();scanner.clear();}catch(e){} scanner=null;}setStatus('Kamera dihentikan.');}
window.addEventListener('load',()=>setTimeout(startScanner,500));
</script></body></html>