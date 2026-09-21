<?php

require_once __DIR__ . "/includes/auth.php";
require_once __DIR__ . "/includes/db.php";

$user_id = (int)$_SESSION["user_id"];


// user_idカラムが存在するか確認
$sql = "
    SHOW COLUMNS
    FROM companies
    LIKE 'user_id'
";

$stmt = $pdo->query($sql);

$column = $stmt->fetch(PDO::FETCH_ASSOC);


// 存在しなければ追加
if (!$column) {

    $sql = "
        ALTER TABLE companies
        ADD COLUMN user_id INT NULL AFTER id
    ";

    $pdo->exec($sql);
}


// 既存の企業データを
// 現在ログイン中のユーザーに紐付ける
$sql = "
    UPDATE companies
    SET user_id = :user_id
    WHERE user_id IS NULL
";

$stmt = $pdo->prepare($sql);

$stmt->bindValue(
    ":user_id",
    $user_id,
    PDO::PARAM_INT
);

$stmt->execute();


// 今後user_idを必須にする
$sql = "
    ALTER TABLE companies
    MODIFY user_id INT NOT NULL
";

$pdo->exec($sql);

echo "companiesテーブルをユーザー対応にしました。";