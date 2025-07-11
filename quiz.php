<?php
require_once('funcs.php');
$pdo = db_conn();
session_start();

// 出題形式
$mode = $_GET['mode'] ?? 'text'; // 'choice' or 'text'

// チェック済みのみ出題
$checked_only = isset($_GET['checked_only']) ? true : false;

// 単語取得（苦手順で、チェック済みのみならWHERE checked=1）
$where = $checked_only ? "WHERE checked=1" : "";
$stmt = $pdo->query("
    SELECT *, 
    CASE 
        WHEN (correct_count + wrong_count) = 0 THEN 1
        ELSE correct_count / (correct_count + wrong_count)
    END AS accuracy
    FROM german_words
    $where
    ORDER BY accuracy ASC, wrong_count DESC, RAND()
    LIMIT 1
");
$word = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$word) {
    echo "出題できる単語がありません。";
    exit();
}

// 回答処理
$feedback = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_answer = $_POST['answer'] ?? '';
    $is_correct = false;
    if ($mode === 'choice') {
        $is_correct = ($user_answer === $word['meaning']);
    } else {
        $is_correct = (trim($user_answer) === trim($word['meaning']));
    }
    if ($is_correct) {
        $pdo->prepare("UPDATE german_words SET correct_count = correct_count + 1 WHERE id=?")->execute([$word['id']]);
        $feedback = "<span style='color:green;'>正解！</span>";
    } else {
        $pdo->prepare("UPDATE german_words SET wrong_count = wrong_count + 1 WHERE id=?")->execute([$word['id']]);
        $feedback = "<span style='color:red;'>不正解！ 正解は「" . h($word['meaning']) . "」です。</span>";
    }
    // 次の問題へリダイレクト
    header("Refresh:1; url=quiz.php?mode=$mode" . ($checked_only ? "&checked_only=1" : ""));
    echo $feedback;
    exit();
}

// 選択肢生成（選択式の場合）
$choices = [];
if ($mode === 'choice') {
    $choices[] = $word['meaning'];
    // 他の選択肢をランダムに3つ追加
    $stmt = $pdo->prepare("SELECT meaning FROM german_words WHERE id != ? ORDER BY RAND() LIMIT 3");
    $stmt->execute([$word['id']]);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $choices[] = $row['meaning'];
    }
    shuffle($choices);
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <title>クイズ</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header>
    <nav>
        <a href="index.php">単語登録</a>
        <a href="select.php">単語リスト</a>
        <a href="quiz.php">クイズ</a>
    </nav>
</header>
<main>
    <h1>クイズ</h1>
    <form method="get" action="">
        <label>
            <input type="radio" name="mode" value="choice" <?= $mode === 'choice' ? 'checked' : '' ?>> 選択式
        </label>
        <label>
            <input type="radio" name="mode" value="text" <?= $mode === 'text' ? 'checked' : '' ?>> 記述式
        </label>
        <label>
            <input type="checkbox" name="checked_only" value="1" <?= $checked_only ? 'checked' : '' ?>> チェック済みのみ
        </label>
        <button type="submit">形式を切り替え</button>
    </form>
    <hr>
    <form method="post">
        <p>「<?= h($word['word']) ?>」の日本語訳は？</p>
        <?php if ($mode === 'choice'): ?>
            <?php foreach ($choices as $opt): ?>
                <label><input type="radio" name="answer" value="<?= h($opt) ?>" required> <?= h($opt) ?></label><br>
            <?php endforeach; ?>
        <?php else: ?>
            <input type="text" name="answer" required>
        <?php endif; ?>
        <button type="submit">答える</button>
    </form>
</main>
</body>
</html>
