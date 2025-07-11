<?php
function db_conn() {
    try {
        return new PDO('mysql:dbname=gsw9;charset=utf8;host=localhost','root','');
    } catch (PDOException $e) {
        exit('DBConnectError:'.$e->getMessage());
    }
}

function h($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}
?>
