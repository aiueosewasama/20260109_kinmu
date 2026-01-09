<?php
require_once 'db.php';

// URLの ?id=xx から従業員IDを取得
$id = $_GET['id'] ?? null;

if (!$id) {
    echo "従業員IDが指定されていません。";
    exit;
}

// -----------------------------------------
// 1. その従業員の基本情報（名前・時給）を取得
// -----------------------------------------
$stmt_user = $pdo->prepare("SELECT * FROM jugyoin WHERE id = :id");
$stmt_user->bindValue(':id', $id, PDO::PARAM_INT);
$stmt_user->execute();
$employee = $stmt_user->fetch(PDO::FETCH_ASSOC);

if (!$employee) {
    echo "従業員が見つかりません。";
    exit;
}

// 時給（未設定なら1350円）
$hourly_wage = !empty($employee['jikyu']) ? $employee['jikyu'] : 1350;

// -----------------------------------------
// 2. その人の出退勤記録を取得
// -----------------------------------------
$stmt_log = $pdo->prepare("SELECT * FROM kiroku WHERE jugyoin_id = :id ORDER BY start_work DESC");
$stmt_log->bindValue(':id', $id, PDO::PARAM_INT);
$stmt_log->execute();
$logs = $stmt_log->fetchAll(PDO::FETCH_ASSOC);

// 合計給料計算用
$total_salary = 0;
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($employee['name']) ?>さんの記録</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* 個人ページ専用のスタイル（薄い紫） */
        .page-member { background-color: #f3e5f5; border: 1px solid #e1bee7; }
        .page-member h1 { color: #8e24aa; }
        .info-box {
            background-color: #fff;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
        }
        .total-salary {
            font-size: 1.2em;
            color: #d81b60;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="nav">
        <a href="clockin.php">出勤登録</a> | 
        <a href="clockout.php">退勤登録</a> | 
        <a href="index.php">全記録一覧</a>
    </div>

    <div class="container page-member">
        <h1><?= htmlspecialchars($employee['name']) ?> さんの記録</h1>

        <div class="info-box">
            <p><strong>適用時給:</strong> <?= number_format($hourly_wage) ?> 円</p>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>日付</th>
                    <th>出勤</th>
                    <th>退勤</th>
                    <th>時間</th>
                    <th>給料</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $row): ?>
                    <?php
                        $salary_display = "-";
                        $duration_display = "-";
                        $salary = 0;

                        if (!empty($row['end_work'])) {
                            $start = strtotime($row['start_work']);
                            $end   = strtotime($row['end_work']);
                            $seconds = $end - $start;
                            $hours = $seconds / 3600;

                            // 給料計算 (時給 × 時間)
                            $salary = floor($hours * $hourly_wage);
                            $salary_display = number_format($salary) . "円";
                            $duration_display = round($hours, 2) . "h";

                            // 合計に加算
                            $total_salary += $salary;
                        }
                    ?>
                <tr>
                    <td><?= date('m/d', strtotime($row['start_work'])) ?></td>
                    <td><?= date('H:i', strtotime($row['start_work'])) ?></td>
                    <td><?= !empty($row['end_work']) ? date('H:i', strtotime($row['end_work'])) : '勤務中' ?></td>
                    <td><?= $duration_display ?></td>
                    <td><?= $salary_display ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="info-box" style="margin-top: 20px; text-align: right;">
            合計支給額: <span class="total-salary"><?= number_format($total_salary) ?> 円</span>
        </div>
    </div>
</body>
</html>