<?php
require_once('funcs.php');
$id = intval($_GET['id'] ?? 0);
$pdo = db_conn();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $word = $_POST['word'];
    $meaning = $_POST['meaning'];
    $comment = $_POST['comment'];
    $stmt = $pdo->prepare("UPDATE german_words SET word=?, meaning=?, comment=? WHERE id=?");
    $stmt->execute([$word, $meaning, $comment, $id]);
    header('Location: select.php');
    exit();
}
$stmt = $pdo->prepare("SELECT * FROM german_words WHERE id=?");
$stmt->execute([$id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<form method="post">
  <input type="text" name="word" value="<?= h($row['word']) ?>" required>
  <input type="text" name="meaning" value="<?= h($row['meaning']) ?>" required>
  <textarea name="comment"><?= h($row['comment']) ?></textarea>
  <button type="submit">更新</button>
</form>
