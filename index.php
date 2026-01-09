<?php
require_once 'db.php';

// ---------------------------------------------------
// データを取得（テーブル結合）
// ---------------------------------------------------
// kirokuテーブル(k) と jugyoinテーブル(j) を結合します
$sql = "SELECT k.*, j.name 
        FROM kiroku k
        JOIN jugyoin j ON k.jugyoin_id = j.id
        ORDER BY k.start_work DESC";

$stmt = $pdo->query($sql);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>全記録一覧</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="nav">
        <a href="clockin.php">出勤登録</a> | 
        <a href="clockout.php">退勤登録</a> | 
        <a href="index.php">全記録一覧</a>
    </div>

    <div class="container page-list">
        <h1>全記録一覧</h1>
        
        <table>
            <thead>
                <tr>
                    <th>氏名</th>
                    <th>出勤時刻</th>
                    <th>退勤時刻</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($results as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    
                    <td><?= htmlspecialchars($row['start_work']) ?></td>
                    <td><?= htmlspecialchars($row['end_work'] ?? '勤務中') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>