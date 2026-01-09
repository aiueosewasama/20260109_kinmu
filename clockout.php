<?php
require_once 'db.php';

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['jugyoin_id'];
    if (!empty($id)) {
        try {
            // 退勤記録をUPDATE（まだ退勤していない最新のレコードを更新）
            // 条件: IDが一致 かつ end_work が NULL
            $sql = "UPDATE kiroku SET end_work = NOW() 
                    WHERE jugyoin_id = :id AND end_work IS NULL 
                    ORDER BY start_work DESC LIMIT 1";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $message = "従業員ID: {$id} さんの退勤を記録しました。";
            } else {
                $message = "エラー：出勤記録が見つからないか、既に退勤済みです。";
            }
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
    <title>退勤登録</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="nav">
        <a href="clockin.php">出勤登録</a> | 
        <a href="clockout.php">退勤登録</a> | 
        <a href="index.php">全記録一覧</a>
    </div>

    <div class="container page-clockout">
        <h1>退勤時刻入力</h1>

        <?php if($message): ?>
            <p class="message"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>

        <form method="post" action="">
            <p>従業員IDを入力してください:</p>
            <input type="number" name="jugyoin_id" required placeholder="例: 101">
            <button type="submit" class="btn-out">退勤する</button>
        </form>
    </div>
</body>
</html>