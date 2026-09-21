<?php

// セッションがまだ開始されていない場合だけ開始
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


// ログインしていない場合
if (!isset($_SESSION["user_id"])) {

    header("Location: login.php");
    exit;
}

?>