<?php
require_once 'db.php'; // データベース接続を読み込み

$message = "";

// POST送信があった場合（ボタンが押された時）
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['jugyoin_id'];
    if (!empty($id)) {
        try {
            // 出勤記録をINSERT (現在時刻をセット)
            $sql = "INSERT INTO kiroku (jugyoin_id, start_work) VALUES (:id, NOW())";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $message = "従業員ID: {$id} さんの出勤を記録しました。";
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
    <link rel="stylesheet" href="style.css"> </head>
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
            <p>従業員IDを入力してください:</p>
            <input type="number" name="jugyoin_id" required placeholder="例: 101">
            <button type="submit" class="btn-in">出勤する</button>
        </form>
    </div>
</body>
</html>