<?php
require_once('funcs.php');
$id = intval($_GET['id'] ?? 0);
$pdo = db_conn();
$stmt = $pdo->prepare("DELETE FROM german_words WHERE id=?");
$stmt->execute([$id]);
header('Location: select.php');
exit();
?>
