<?php
require_once 'db.php';

$message = "";

// -----------------------------------------
// 1. 従業員リストを取得 (プルダウン用)
// -----------------------------------------
try {
    $stmt = $pdo->query("SELECT id, name FROM jugyoin");
    $jugyoin_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "従業員データの取得に失敗: " . $e->getMessage();
    exit;
}

// -----------------------------------------
// 2. 出勤ボタンが押された時の処理
// -----------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['jugyoin_id']; // プルダウンで選ばれたID
    if (!empty($id)) {
        try {
            $sql = "INSERT INTO kiroku (jugyoin_id, start_work) VALUES (:id, NOW())";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            // IDから名前を特定してメッセージを出す（任意）
            $message = "出勤を記録しました！ (ID: {$id})";
        } catch (PDOException $e) {
            $message = "エラーが発生しました: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>出勤登録</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="nav">
        <a href="clockin.php">出勤登録</a> | 
        <a href="clockout.php">退勤登録</a> | 
        <a href="index.php">全記録一覧</a>
    </div>

    <div class="container page-clockin">
        <h1>出勤時刻入力</h1>
        
        <?php if($message): ?>
            <p class="message"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>

        <form method="post" action="">
            <p>氏名を選択してください:</p>
            
            <select name="jugyoin_id" required style="width: 100%; padding: 10px; font-size: 16px; margin-bottom: 10px;">
                <option value="">-- 選択してください --</option>
                <?php foreach ($jugyoin_list as $employee): ?>
                    <option value="<?= htmlspecialchars($employee['id']) ?>">
                        <?= htmlspecialchars($employee['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn-in">出勤する</button>
        </form>
    </div>
</body>
</html>