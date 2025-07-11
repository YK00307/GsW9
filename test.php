<?php
require_once('funcs.php');
$pdo = db_conn();

session_start();

// 初回アクセス時、単語リストをシャッフルしてセッションに保存
if (!isset($_SESSION['test_words'])) {
    $stmt = $pdo->query("SELECT word, meaning FROM gs_bm_table ORDER BY RAND()");
    $_SESSION['test_words'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $_SESSION['test_index'] = 0;
    $_SESSION['test_score'] = 0;
    $_SESSION['test_total'] = count($_SESSION['test_words']);
}

// テストが終わった場合
if (isset($_POST['restart'])) {
    unset($_SESSION['test_words'], $_SESSION['test_index'], $_SESSION['test_score'], $_SESSION['test_total']);
    header('Location: test.php');
    exit();
}

// 回答処理
$feedback = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['answer'])) {
    $index = $_SESSION['test_index'];
    $correct = $_SESSION['test_words'][$index]['meaning'];
    $user_answer = trim($_POST['answer']);
    if ($user_answer === $correct) {
        $_SESSION['test_score']++;
        $feedback = '<span class="correct">正解！</span>';
    } else {
        $feedback = '<span class="wrong">不正解！正解は「'.h($correct).'」です。</span>';
    }
    $_SESSION['test_index']++;
}

// テスト終了判定
$index = $_SESSION['test_index'];
$total = $_SESSION['test_total'];
$score = $_SESSION['test_score'];

// チェックされた単語IDだけを使う
if (isset($_POST['word_ids'])) {
    $ids = array_map('intval', $_POST['word_ids']);
    $in  = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT word, meaning FROM german_words WHERE id IN ($in) ORDER BY RAND()");
    $stmt->execute($ids);
    $words = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // ...以降は既存のテストロジック
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <title>ドイツ語単語テスト</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <nav>
            <a href="index.php">単語登録</a>
            <a href="select.php">単語リスト</a>
        </nav>
    </header>
    <main>
        <h1>ドイツ語テスト</h1>
        <?php if ($total === 0): ?>
            <p>テストできる単語がありません。</p>
        <?php elseif ($index >= $total): ?>
            <p>テスト終了！あなたのスコアは <?= $score ?> / <?= $total ?> です。</p>
            <form method="post">
                <button type="submit" name="restart">もう一度テストする</button>
            </form>
        <?php else: ?>
            <div class="test-box">
                <p><strong>ドイツ語：</strong> <?= h($_SESSION['test_words'][$index]['word']) ?></p>
                <form method="post">
                    <label for="answer">日本語訳を入力：</label>
                    <input type="text" id="answer" name="answer" required autofocus>
                    <button type="submit">答える</button>
                </form>
                <?php if ($feedback): ?>
                    <div class="feedback"><?= $feedback ?></div>
                <?php endif; ?>
                <p>進捗: <?= $index ?> / <?= $total ?></p>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>
