<?php
$host = 'localhost';
$dbname = 'kintaidb';
$user = 'kintaiuser';
$pass = 'kintaipass123';

// ★ここに追加：PHPのタイムゾーンを日本に設定
date_default_timezone_set('Asia/Tokyo');

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // ★ここにも追加：MySQLとのセッションタイムゾーンを日本にする
    $pdo->exec("SET time_zone = '+09:00'");
    
} catch (PDOException $e) {
    die("接続エラー: " . $e->getMessage());
}