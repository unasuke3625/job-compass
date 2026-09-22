<?php

require_once __DIR__ . "/includes/auth.php";
require_once __DIR__ . "/includes/db.php";
require_once __DIR__ . "/includes/functions.php";

$user_id = (int)$_SESSION["user_id"];

$id = $_GET["id"] ?? "";

if (!ctype_digit($id)) {
    exit("不正なIDです。");
}


// ------------------------------
// 削除対象の企業情報を取得
// ------------------------------

$sql = "
    SELECT *
    FROM companies
    WHERE id = :id
    AND user_id = :user_id
";

$stmt = $pdo->prepare($sql);

$stmt->bindValue(
    ":id",
    (int)$id,
    PDO::PARAM_INT
);

$stmt->bindValue(
    ":user_id",
    $user_id,
    PDO::PARAM_INT
);

$stmt->execute();

$company = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$company) {
    exit("企業情報が見つかりません。");
}


// ------------------------------
// 削除ボタンが押された場合
// ------------------------------

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $delete_id = $_POST["delete_id"] ?? "";

    if (!ctype_digit($delete_id)) {
        exit("不正なIDです。");
    }


    $sql = "
        DELETE FROM companies
        WHERE id = :id
        AND user_id = :user_id
    ";
    
    $stmt = $pdo->prepare($sql);
    
    $stmt->bindValue(
        ":id",
        (int)$id,
        PDO::PARAM_INT
    );
    
    $stmt->bindValue(
        ":user_id",
        $user_id,
        PDO::PARAM_INT
    );
    
    $stmt->execute();


    header("Location: index.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <link rel="stylesheet" href="css/style.css">

    <title>企業削除 | Job Compass</title>
</head>

<body>

<div class="app-layout">

    <!-- =========================
         サイドバー
    ========================== -->
    <aside class="sidebar">

        <div class="sidebar-logo">
            Job<span>Compass</span>
        </div>

        <nav class="sidebar-nav">

            <a href="index.php">
                ダッシュボード
            </a>

            <a href="selection_list.php">
                選考管理
            </a>

            <a href="#">
                スケジュール
            </a>
            
            <a href="logout.php">
                ログアウト
            </a>

        </nav>

    </aside>


    <!-- =========================
         メイン画面
    ========================== -->
    <main class="main-area">

        <header class="page-header">

            <div>

                <h1 class="page-title">
                    企業情報を削除
                </h1>

                <div class="page-description">
                    登録済みの企業情報を削除します
                </div>

            </div>

        </header>


        <!-- =========================
             削除確認
        ========================== -->
        <div class="confirm-card">

            <div class="confirm-header">

                <h2 class="confirm-title">
                    この企業を削除しますか？
                </h2>

                <p class="confirm-description">
                    削除する企業情報を確認してください
                </p>

            </div>


            <div class="delete-warning">
                削除した企業情報は元に戻せません。
            </div>


            <div class="confirm-details">

                <div class="confirm-row">

                    <div class="confirm-label">
                        企業名
                    </div>

                    <div class="confirm-value">
                        <?= h($company["company_name"]) ?>
                    </div>

                </div>


                <div class="confirm-row">

                    <div class="confirm-label">
                        業界
                    </div>

                    <div class="confirm-value">
                        <?= h($company["industry"] ?? "") ?>
                    </div>

                </div>


                <div class="confirm-row">

                    <div class="confirm-label">
                        希望職種
                    </div>

                    <div class="confirm-value">
                        <?= h($company["job_type"] ?? "") ?>
                    </div>

                </div>


                <div class="confirm-row">

                    <div class="confirm-label">
                        志望度
                    </div>

                    <div class="confirm-value">
                        <?= h($company["interest_level"] ?? "") ?>
                    </div>

                </div>


                <div class="confirm-row">

                    <div class="confirm-label">
                        選考状況
                    </div>

                    <div class="confirm-value">
                        <?= h($company["status"] ?? "") ?>
                    </div>

                </div>

            </div>


            <form
                method="POST"
                class="form-actions"
            >

                <input
                    type="hidden"
                    name="delete_id"
                    value="<?= h($company["id"]) ?>"
                >


                <a
                    href="index.php"
                    class="btn btn-secondary"
                >
                    キャンセル
                </a>


                <button
                    type="submit"
                    class="btn btn-danger"
                >
                    企業を削除
                </button>

            </form>

        </div>

    </main>

</div>

</body>
</html>