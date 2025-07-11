<?php require_once('funcs.php'); ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>ドイツ語単語登録</title>
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
    <h1>ドイツ語単語登録</h1>
    <form method="post" action="insert.php">
        <div style="display: flex; align-items: center;">
            <input type="text" id="word" name="word" required placeholder="ドイツ語単語" style="flex:1;">
            <button type="button" id="imgInputBtn" title="画像から入力" style="margin-left:8px; padding:4px 8px; font-size:0.9em;">📷</button>
            <input type="file" id="imgInput" accept="image/*" capture="environment" style="display:none;">
        </div>
        <div>
            <label for="meaning">日本語訳</label><br>
            <input type="text" id="meaning" name="meaning" required>
        </div>
        <div>
            <label for="comment">コメント</label><br>
            <textarea id="comment" name="comment" rows="3"></textarea>
        </div>
        <div>
            <button type="submit">登録する</button>
        </div>
    </form>
</main>

<script>
document.getElementById('imgInputBtn').addEventListener('click', function() {
    document.getElementById('imgInput').click();
});
document.getElementById('imgInput').addEventListener('change', async function(e) {
    const file = e.target.files[0];
    if (!file) return;
    const formData = new FormData();
    formData.append('image', file);

    // OCR用APIに画像送信
    const ocrRes = await fetch('ocr.php', { method: 'POST', body: formData });
    const { text } = await ocrRes.json();
    document.getElementById('word').value = text;

    // 翻訳APIに送信して日本語訳取得
    const transRes = await fetch('translate.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ text })
    });
    const { translation } = await transRes.json();
    document.getElementById('meaning').value = translation;
});
</script>
</body>
</html>
