<?php
// ---------------------------------------------
// 1. .env ファイルを読み込む処理
// ---------------------------------------------
$envFile = __DIR__ . '/.env';

if (file_exists($envFile)) {
    // ファイルを1行ずつ配列として読み込む
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        // # で始まる行（コメント）は無視
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        // 「=」で区切って名前と値に分ける
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            
            // 環境変数として保存（どこからでも使えるようになる）
            putenv("$key=$value");
            $_ENV[$key] = $value;
        }
    }
} else {
    // .envがない場合はエラー終了（セキュリティのため）
    die('エラー: .env ファイルが見つかりません。');
}

// ---------------------------------------------
// 2. データベース接続
// ---------------------------------------------
// さきほど読み込んだ環境変数を使う
$host = getenv('HOST');
$dbname = getenv('DBNAME');
$user = getenv('USER');
$pass = getenv('PASS');

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