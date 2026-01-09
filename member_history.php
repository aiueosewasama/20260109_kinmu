<?php
require_once 'db.php';

// URLの ?id=xx から従業員IDを取得
$id = $_GET['id'] ?? null;
$message = ""; // 更新メッセージ用

if (!$id) {
    echo "従業員IDが指定されていません。";
    exit;
}

// -----------------------------------------
// 0. 時給変更ボタンが押された場合の処理 (POST)
// -----------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_jikyu'])) {
    $new_jikyu = (int)$_POST['new_jikyu'];
    
    if ($new_jikyu > 0) {
        try {
            $sql_update = "UPDATE jugyoin SET jikyu = :jikyu WHERE id = :id";
            $stmt_update = $pdo->prepare($sql_update);
            $stmt_update->bindValue(':jikyu', $new_jikyu, PDO::PARAM_INT);
            $stmt_update->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt_update->execute();
            
            $message = "時給を {$new_jikyu}円 に変更しました！";
        } catch (PDOException $e) {
            $message = "更新エラー: " . $e->getMessage();
        }
    }
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

$total_salary = 0;
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($employee['name']) ?>さんの記録</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .page-member { background-color: #f3e5f5; border: 1px solid #e1bee7; }
        .page-member h1 { color: #8e24aa; }
        .info-box {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            display: flex; /* 横並びにする */
            justify-content: space-between;
            align-items: center;
        }
        .total-salary {
            font-size: 1.4em;
            color: #d81b60;
            font-weight: bold;
        }
        /* 更新フォームのデザイン */
        .wage-form {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .wage-input {
            padding: 5px;
            font-size: 16px;
            width: 80px;
            text-align: right;
        }
        .btn-update {
            background-color: #8e24aa;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn-update:hover { background-color: #7b1fa2; }
        .success-msg { color: #2e7d32; font-weight: bold; margin-bottom: 10px; }
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

        <?php if($message): ?>
            <p class="success-msg"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>

        <div class="info-box">
            <div class="wage-section">
                <form method="post" class="wage-form">
                    <label>現在の時給:</label>
                    <input type="number" name="new_jikyu" value="<?= $hourly_wage ?>" class="wage-input" required>
                    <span>円</span>
                    <button type="submit" class="btn-update">変更</button>
                </form>
                <small style="color:#666; display:block; margin-top:5px;">※変更すると過去の給料計算も全て新時給で再計算されます</small>
            </div>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>日付</th>
                    <th>出勤</th>
                    <th>退勤</th>
                    <th>時間</th>
                    <th>給料 (<?= $hourly_wage ?>円計算)</th>
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

                            // 現在設定されている時給で計算
                            $salary = floor($hours * $hourly_wage);
                            $salary_display = number_format($salary) . "円";
                            $duration_display = round($hours, 2) . "h";

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

        <div class="info-box" style="justify-content: flex-end;">
            合計支給額: <span class="total-salary" style="margin-left: 10px;"><?= number_format($total_salary) ?> 円</span>
        </div>
    </div>
</body>
</html>