<?php
session_start();
$BASE_URL = rtrim(str_replace('\\','/',dirname(dirname($_SERVER['SCRIPT_NAME'] ?? '/'))),'/');
if ($BASE_URL === '/' || $BASE_URL === '.') $BASE_URL = '';
$pdo=new PDO('mysql:host=127.0.0.1;dbname=perpustakaan_smpit;charset=utf8mb4','root','',[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
function e($x){return htmlspecialchars((string)$x,ENT_QUOTES,'UTF-8');} function login(){if(empty($_SESSION['u'])){header('Location:'.$BASE_URL.'/public/login.php');exit;}} function role($r){login();if(!in_array($_SESSION['u']['role'],(array)$r,true))die('Akses ditolak');} function setmsg($m,$c='success'){$_SESSION['msg']=[$m,$c];} function msg(){if(isset($_SESSION['msg'])){[$m,$c]=$_SESSION['msg'];unset($_SESSION['msg']);echo '<div class="alert '.e($c).'">'.e($m).'</div>';}} function setting($k,$d){global $pdo;$s=$pdo->prepare('SELECT value FROM settings WHERE `key`=?');$s->execute([$k]);return $s->fetchColumn()??$d;} function late($d){return max(0,(new DateTime($d))->diff(new DateTime())->days*(((new DateTime($d))->getTimestamp()<(new DateTime())->getTimestamp())?1:0));}
?>
