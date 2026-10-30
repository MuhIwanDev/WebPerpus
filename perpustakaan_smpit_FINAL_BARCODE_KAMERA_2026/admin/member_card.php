<?php
require '../config/config.php';
role('administrator');

$ids = [];
if (isset($_GET['id'])) $ids[] = (int)$_GET['id'];
if (isset($_GET['ids']) && is_array($_GET['ids'])) $ids = array_values(array_filter(array_map('intval', $_GET['ids'])));
$all = isset($_GET['all']) && $_GET['all']=='1';

if ($all) {
    $rows = $pdo->query("SELECT id,name,username,class_name,status FROM users WHERE role='anggota' AND status='aktif' ORDER BY class_name,name")->fetchAll();
} elseif ($ids) {
    $ph = implode(',', array_fill(0,count($ids),'?'));
    $s=$pdo->prepare("SELECT id,name,username,class_name,status FROM users WHERE role='anggota' AND id IN ($ph) ORDER BY class_name,name");
    $s->execute($ids); $rows=$s->fetchAll();
} else {
    $rows=[];
}

if (!$rows) die('Tidak ada anggota yang dipilih. <a href="users.php">Kembali ke Manajemen Akun</a>');
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Cetak Kartu Anggota - Perpustakaan SMPIT</title>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jsbarcode/3.11.6/JsBarcode.all.min.js"></script>
<style>
*{box-sizing:border-box}
body{margin:0;background:#eee;font-family:Arial,sans-serif;color:#102f63}
.toolbar{padding:12px;text-align:center;background:#fff;position:sticky;top:0;z-index:5;border-bottom:1px solid #ddd}
button,a{padding:9px 16px;border:0;border-radius:7px;background:#0b57d0;color:#fff;text-decoration:none;cursor:pointer;margin:3px}
.sheet{display:grid;grid-template-columns:repeat(2,85.6mm);gap:5mm 6mm;justify-content:center;padding:12mm 8mm}
.member-card{width:85.6mm;height:54mm;background:#fff;border:1px solid #b9c9df;border-radius:3mm;position:relative;overflow:hidden;padding:5mm;page-break-inside:avoid}
.top{display:flex;align-items:center;gap:3mm;border-bottom:1px solid #d7e0ee;padding-bottom:2.5mm}
.logo{width:15mm;height:15mm;object-fit:contain}
.school{font-weight:800;font-size:11pt;line-height:1.1}.sub{font-size:7pt;margin-top:1mm}
.title{text-align:center;font-size:10pt;font-weight:800;margin:2.5mm 0 1.5mm}
.info{font-size:7.5pt;line-height:1.55}.info b{display:inline-block;width:19mm}
.barcode{display:block;margin:1.5mm auto 0;width:65mm;height:12mm}
.code{text-align:center;font-size:7pt;letter-spacing:1px}
@media print{
  body{background:#fff}.toolbar{display:none}.sheet{padding:0;gap:5mm 6mm}
  @page{size:A4;margin:10mm}
}
</style>
</head>
<body>
<div class="toolbar">
  <b>Pratinjau Kartu Anggota</b>
  <button onclick="window.print()">🖨️ Cetak</button>
  <a href="users.php">← Kembali</a>
</div>
<div class="sheet">
<?php foreach($rows as $r): $value=(string)$r['username']; ?>
<div class="member-card">
  <div class="top">
    <img class="logo" src="../assets/logo-perpustakaan.png">
    <div><div class="school">PERPUSTAKAAN SMPIT</div><div class="sub">SMPIT Sahabat Bintang</div></div>
  </div>
  <div class="title">KARTU ANGGOTA PERPUSTAKAAN</div>
  <div class="info">
    <div><b>Nama</b>: <?=e($r['name'])?></div>
    <div><b>NIS/Username</b>: <?=e($r['username'])?></div>
    <div><b>Kelas</b>: <?=e($r['class_name'])?></div>
  </div>
  <svg class="barcode" data-value="<?=e($value)?>"></svg>
  <div class="code"><?=e($value)?></div>
</div>
<?php endforeach; ?>
</div>
<script>
document.querySelectorAll('.barcode').forEach(function(el){
  try {
    JsBarcode(el, el.dataset.value, {
      format:"CODE128", displayValue:false, width:1.35, height:38,
      margin:0, background:"#ffffff", lineColor:"#000000"
    });
  } catch(e) {
    el.outerHTML='<div style="text-align:center;font-size:7pt">Barcode tidak dapat dibuat. Pastikan koneksi internet aktif saat membuka halaman cetak.</div>';
  }
});
</script>
<script>
document.querySelectorAll('.member-barcode').forEach(function(el){
  var code=el.getAttribute('data-code');
  if(window.JsBarcode && code){JsBarcode(el,code,{format:"CODE128",displayValue:true,width:2,height:55,margin:8});}
});
</script></body></html>