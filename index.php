<?php
require_once 'db.php';

// 全員分のデータを取得（テーブル結合）
$sql = "SELECT k.*, j.name, j.jikyu 
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
                    <th>勤務時間</th>
                    <th>給料目安</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($results as $row): ?>
                    <?php
                        // 計算ロジック（前回と同じ）
                        $salary_display = "-";
                        $duration_display = "-";
                        if (!empty($row['end_work'])) {
                            $diff = strtotime($row['end_work']) - strtotime($row['start_work']);
                            $hours = $diff / 3600;
                            $wage = !empty($row['jikyu']) ? $row['jikyu'] : 1350; // 個別時給
                            $salary = floor($hours * $wage);
                            $salary_display = number_format($salary) . "円";
                            $duration_display = round($hours, 2) . "h";
                        }
                    ?>
                <tr>
                    <td>
                        <a href="member_history.php?id=<?= $row['jugyoin_id'] ?>" style="color:#e65100; font-weight:bold;">
                            <?= htmlspecialchars($row['name']) ?>
                        </a>
                    </td>
                    
                    <td><?= date('m/d H:i', strtotime($row['start_work'])) ?></td>
                    <td>
                        <?= !empty($row['end_work']) ? date('m/d H:i', strtotime($row['end_work'])) : '<span style="color:#aaa">勤務中</span>' ?>
                    </td>
                    <td><?= $duration_display ?></td>
                    <td style="font-weight:bold; color:#e65100;"><?= $salary_display ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>