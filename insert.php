<?php
require_once('funcs.php');

$word    = $_POST['word'] ?? '';
$meaning = $_POST['meaning'] ?? '';
$comment = $_POST['comment'] ?? '';

if ($word === '' || $meaning === '') {
    exit('入力項目が未記入です');
}

$pdo = db_conn();
$sql = "INSERT INTO german_words (word, meaning, comment, date) VALUES (:word, :meaning, :comment, NOW())";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':word', $word, PDO::PARAM_STR);
$stmt->bindValue(':meaning', $meaning, PDO::PARAM_STR);
$stmt->bindValue(':comment', $comment, PDO::PARAM_STR);
$status = $stmt->execute();

if ($status === false) {
    $error = $stmt->errorInfo();
    exit('ErrorMessage:'.$error[2]);
} else {
    header('Location: select.php');
    exit();
}
?>
