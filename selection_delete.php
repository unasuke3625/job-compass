<?php

require_once __DIR__ . "/includes/auth.php";
require_once __DIR__ . "/includes/db.php";
require_once __DIR__ . "/includes/functions.php";

$user_id = (int)$_SESSION["user_id"];


// ------------------------------------
// URLから選考IDを取得
// ------------------------------------

if (
    !isset($_GET["id"]) ||
    !is_string($_GET["id"]) ||
    !ctype_digit($_GET["id"])
) {
    exit("不正なIDです。");
}

$selection_id = (int)$_GET["id"];


// ------------------------------------
// 削除対象の選考データを取得
// ------------------------------------

$sql = "
    SELECT
        selections.*,
        companies.company_name
    FROM selections

    INNER JOIN companies
        ON selections.company_id = companies.id

    WHERE selections.id = :id
    AND companies.user_id = :user_id
";

$stmt = $pdo->prepare($sql);

$stmt->bindValue(
    ":id",
    $selection_id,
    PDO::PARAM_INT
);

$stmt->bindValue(
    ":user_id",
    $user_id,
    PDO::PARAM_INT
);

$stmt->execute();

$selection = $stmt->fetch(PDO::FETCH_ASSOC);


// 自分の選考でなければ終了
if (!$selection) {
    exit("選考情報が見つかりません。");
}


// ------------------------------------
// 削除ボタンが押された場合
// ------------------------------------

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $sql = "
        DELETE FROM selections
        WHERE id = :id
        AND company_id IN (
            SELECT id
            FROM companies
            WHERE user_id = :user_id
        )
    ";

    $stmt = $pdo->prepare($sql);

    $stmt->bindValue(
        ":id",
        $selection_id,
        PDO::PARAM_INT
    );

    $stmt->bindValue(
        ":user_id",
        $user_id,
        PDO::PARAM_INT
    );

    $stmt->execute();


    header("Location: selection_list.php");
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

    <title>選考情報削除 | Job Compass</title>
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

            <a
                href="selection_list.php"
                class="active"
            >
                選考管理
            </a>

            <a href="index.php#companies">
                企業管理
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
                    選考情報を削除
                </h1>

                <div class="page-description">
                    登録済みの選考情報を削除します
                </div>

            </div>

        </header>


        <!-- =========================
             削除確認
        ========================== -->
        <div class="confirm-card">

            <div class="confirm-header">

                <h2 class="confirm-title">
                    この選考情報を削除しますか？
                </h2>

                <p class="confirm-description">
                    削除する内容を確認してください
                </p>

            </div>


            <div class="delete-warning">
                削除した選考情報は元に戻せません。
            </div>


            <div class="confirm-details">

                <div class="confirm-row">

                    <div class="confirm-label">
                        企業名
                    </div>

                    <div class="confirm-value">
                        <?= h($selection["company_name"]) ?>
                    </div>

                </div>


                <div class="confirm-row">

                    <div class="confirm-label">
                        選考種別
                    </div>

                    <div class="confirm-value">
                        <?= h($selection["selection_type"]) ?>
                    </div>

                </div>


                <div class="confirm-row">

                    <div class="confirm-label">
                        締切日
                    </div>

                    <div class="confirm-value">

                        <?php if (!empty($selection["deadline"])): ?>

                            <?= h($selection["deadline"]) ?>

                        <?php else: ?>

                            未設定

                        <?php endif; ?>

                    </div>

                </div>


                <div class="confirm-row">

                    <div class="confirm-label">
                        実施日
                    </div>

                    <div class="confirm-value">

                        <?php if (!empty($selection["event_date"])): ?>

                            <?= h($selection["event_date"]) ?>

                        <?php else: ?>

                            未設定

                        <?php endif; ?>

                    </div>

                </div>


                <div class="confirm-row">

                    <div class="confirm-label">
                        開始時刻
                    </div>

                    <div class="confirm-value">

                        <?php if (!empty($selection["start_time"])): ?>

                            <?= h($selection["start_time"]) ?>

                        <?php else: ?>

                            未設定

                        <?php endif; ?>

                    </div>

                </div>


                <div class="confirm-row">

                    <div class="confirm-label">
                        ステータス
                    </div>

                    <div class="confirm-value">
                        <?= h($selection["status"] ?? "") ?>
                    </div>

                </div>

            </div>


            <form
                method="post"
                class="form-actions"
            >

                <a
                    href="selection_list.php"
                    class="btn btn-secondary"
                >
                    キャンセル
                </a>


                <button
                    type="submit"
                    class="btn btn-danger"
                >
                    選考情報を削除
                </button>

            </form>

        </div>

    </main>

</div>

</body>
</html>