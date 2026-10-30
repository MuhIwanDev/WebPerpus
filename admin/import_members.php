<?php
require '../config/config.php';
role('administrator');

$results = null;
$error = '';

function xlsx_cell_value($cell, $shared) {
    $type = (string)$cell['t'];
    $v = $cell->v;
    if ($type === 's') return isset($shared[(int)$v]) ? $shared[(int)$v] : '';
    if ($type === 'inlineStr') return (string)$cell->is->t;
    return (string)$v;
}

function xlsx_col_index($ref) {
    preg_match('/^[A-Z]+/i', $ref, $m);
    $letters = strtoupper($m[0] ?? 'A');
    $n = 0;
    for ($i=0; $i<strlen($letters); $i++) $n = $n * 26 + (ord($letters[$i])-64);
    return $n - 1;
}

function read_xlsx($file) {
    if (!class_exists('ZipArchive')) throw new Exception('Ekstensi ZIP PHP belum aktif. Aktifkan extension=zip di php.ini lalu restart Apache.');
    $zip = new ZipArchive();
    if ($zip->open($file) !== true) throw new Exception('File Excel tidak dapat dibuka.');
    $shared = [];
    $ss = $zip->getFromName('xl/sharedStrings.xml');
    if ($ss !== false) {
        $xml = simplexml_load_string($ss);
        foreach ($xml->si as $si) {
            $text = '';
            if (isset($si->t)) $text = (string)$si->t;
            else foreach ($si->r as $r) $text .= (string)$r->t;
            $shared[] = $text;
        }
    }
    $wb = simplexml_load_string($zip->getFromName('xl/workbook.xml'));
    $rels = simplexml_load_string($zip->getFromName('xl/_rels/workbook.xml.rels'));
    $relsMap = [];
    foreach ($rels->Relationship as $rel) $relsMap[(string)$rel['Id']] = (string)$rel['Target'];
    $ns = $wb->getDocNamespaces(true);
    $sheets = $wb->children($ns['']);
    $sheet = $sheets->sheets->sheet[0];
    $rid = (string)$sheet->attributes('http://schemas.openxmlformats.org/officeDocument/2006/relationships')['id'];
    $target = $relsMap[$rid] ?? 'worksheets/sheet1.xml';
    $path = 'xl/' . ltrim($target, '/');
    if (strpos($target, 'xl/') === 0) $path = $target;
    $sheetXml = $zip->getFromName($path);
    if ($sheetXml === false) throw new Exception('Worksheet Excel tidak ditemukan.');
    $sx = simplexml_load_string($sheetXml);
    $rows = [];
    foreach ($sx->sheetData->row as $row) {
        $vals = [];
        foreach ($row->c as $cell) {
            $idx = xlsx_col_index((string)$cell['r']);
            $vals[$idx] = xlsx_cell_value($cell, $shared);
        }
        if ($vals) {
            $max = max(array_keys($vals));
            $line = [];
            for ($i=0; $i <= $max; $i++) $line[] = trim((string)($vals[$i] ?? ''));
            $rows[] = $line;
        }
    }
    $zip->close();
    return $rows;
}

function read_csv_file($file) {
    $rows = [];
    $h = fopen($file, 'r');
    while (($r = fgetcsv($h, 0, ',')) !== false) {
        if (count($r) === 1 && trim($r[0]) === '') continue;
        $rows[] = array_map('trim', $r);
    }
    fclose($h);
    return $rows;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['excel_file'])) {
    try {
        if ($_FILES['excel_file']['error'] !== UPLOAD_ERR_OK) throw new Exception('Upload file gagal.');
        $name = $_FILES['excel_file']['name'];
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        if (!in_array($ext, ['xlsx','csv'], true)) throw new Exception('Format harus .xlsx atau .csv.');
        if ($_FILES['excel_file']['size'] > 5 * 1024 * 1024) throw new Exception('Ukuran file maksimal 5 MB.');

        $rows = $ext === 'xlsx'
            ? read_xlsx($_FILES['excel_file']['tmp_name'])
            : read_csv_file($_FILES['excel_file']['tmp_name']);

        if (count($rows) < 2) throw new Exception('File tidak memiliki data anggota.');

        $header = array_map(function($v) {
            $v = strtolower(trim($v));
            $v = str_replace([' ','-','/'], '_', $v);
            return $v;
        }, $rows[0]);

        $find = function($names) use ($header) {
            foreach ($names as $n) {
                $idx = array_search($n, $header, true);
                if ($idx !== false) return $idx;
            }
            return null;
        };

        $iName = $find(['nama','name']);
        $iUser = $find(['username','nis','nisn','username_nis']);
        $iClass = $find(['kelas','class','class_name']);
        $iPass = $find(['password','kata_sandi']);

        if ($iName === null || $iUser === null) {
            throw new Exception('Kolom wajib tidak ditemukan. Gunakan kolom: nama, username/nis, kelas, password.');
        }

        $ok = 0; $skip = 0; $fail = [];
        $stmt = $pdo->prepare("INSERT INTO users(name,username,password,role,status,class_name) VALUES(?,?,?,?,?,?)");
        foreach (array_slice($rows, 1) as $num => $row) {
            $line = $num + 2;
            $nameVal = trim((string)($row[$iName] ?? ''));
            $userVal = trim((string)($row[$iUser] ?? ''));
            $classVal = $iClass !== null ? trim((string)($row[$iClass] ?? '')) : '';
            $passVal = $iPass !== null ? trim((string)($row[$iPass] ?? '')) : '';
            if ($nameVal === '' && $userVal === '') continue;
            if ($nameVal === '' || $userVal === '') {
                $fail[] = "Baris $line: nama dan username/NIS wajib diisi.";
                continue;
            }
            if ($passVal === '') $passVal = 'Perpus123!';
            try {
                $stmt->execute([$nameVal, $userVal, password_hash($passVal, PASSWORD_DEFAULT), 'anggota', 'aktif', $classVal]);
                $ok++;
            } catch (PDOException $e) {
                if ((int)$e->errorInfo[1] === 1062) {
                    $skip++;
                    $fail[] = "Baris $line: username/NIS '$userVal' sudah terdaftar, dilewati.";
                } else {
                    $fail++;
                    $fail[] = "Baris $line: gagal disimpan.";
                }
            }
        }
        $results = ['ok'=>$ok,'skip'=>$skip,'fail'=>$fail];
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}
?>
<!doctype html>
<html lang="id">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Import Anggota dari Excel</title><link rel="stylesheet" href="../public/style.css"></head>
<body><div class="layout"><?php include '../public/nav.php'; ?><main class="main">
<h1>📥 Import Anggota dari Excel</h1>
<?php msg(); if($error): ?><div class="alert danger"><?=e($error)?></div><?php endif; ?>

<div class="card">
<h2>Tambahkan Banyak Anggota Sekaligus</h2>
<p>Upload file Excel <b>.xlsx</b> atau CSV. Sistem akan membuat akun dengan role <b>anggota</b> dan status <b>aktif</b>.</p>
<p><b>Kolom wajib:</b> Nama, Username/NIS, Kelas. Kolom Password boleh dikosongkan; default password adalah <code>Perpus123!</code>.</p>
<p><a class="btn" href="template_anggota.xlsx">⬇ Download Template Excel</a></p>
<form method="post" enctype="multipart/form-data">
<label>Pilih file Excel</label>
<input type="file" name="excel_file" accept=".xlsx,.csv" required>
<br><button>📥 Import Anggota</button>
</form>
</div>

<?php if($results): ?>
<div class="card">
<h2>Hasil Import</h2>
<p>✅ Berhasil dibuat: <b><?=$results['ok']?></b> akun</p>
<p>⏭️ Dilewati: <b><?=$results['skip']?></b> akun</p>
<?php if($results['fail']): ?><h3>Catatan</h3><ul><?php foreach($results['fail'] as $x): ?><li><?=e($x)?></li><?php endforeach; ?></ul><?php endif; ?>
</div>
<?php endif; ?>

<div class="card">
<h2>Contoh Format</h2>
<table class="table"><tr><th>nama</th><th>username</th><th>kelas</th><th>password</th></tr>
<tr><td>Ahmad Fauzan</td><td>2026001</td><td>7A</td><td>Perpus123!</td></tr>
<tr><td>Siti Aisyah</td><td>2026002</td><td>7A</td><td></td></tr></table>
</div>
</main></div></body></html>
