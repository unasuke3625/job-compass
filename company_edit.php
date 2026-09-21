<?php

require_once __DIR__ . "/includes/auth.php";
require_once __DIR__ . "/includes/db.php";
require_once __DIR__ . "/includes/functions.php";

$user_id = (int)$_SESSION["user_id"];

$message = "";

// ------------------------------
// 編集対象のIDを取得
// ------------------------------

$id = $_GET["id"] ?? "";


// IDが数字か確認
if (!ctype_digit($id)) {
    exit("不正なIDです。");
}


// ------------------------------
// 現在の企業情報を取得
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

$company = $stmt->fetch();


// 対象企業が存在しなければ終了
if (!$company) {
    exit("企業情報が見つかりません。");
}


// ------------------------------
// 編集フォームが送信された場合
// ------------------------------

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $company_name = trim($_POST["company_name"] ?? "");
    $industry = trim($_POST["industry"] ?? "");
    $job_type = trim($_POST["job_type"] ?? "");
    $application_route = trim($_POST["application_route"] ?? "");
    $service_name = trim($_POST["service_name"] ?? "");

    $login_url = trim($_POST["login_url"] ?? "");
    $login_email = trim($_POST["login_email"] ?? "");
    $login_id = trim($_POST["login_id"] ?? "");
    $password_management = trim($_POST["password_management"] ?? "");

    $interest_level = $_POST["interest_level"] ?? "";
    $status = $_POST["status"] ?? "";
    $memo = trim($_POST["memo"] ?? "");


    if ($company_name !== "") {

        $sql = "
            UPDATE companies
            SET
                company_name = :company_name,
                industry = :industry,
                job_type = :job_type,
                application_route = :application_route,
                service_name = :service_name,
                login_url = :login_url,
                login_email = :login_email,
                login_id = :login_id,
                password_management = :password_management,
                interest_level = :interest_level,
                status = :status,
                memo = :memo
            WHERE id = :id
            AND user_id = :user_id
        ";

        $stmt = $pdo->prepare($sql);
        
        $stmt->bindValue(
            ":user_id",
            $user_id,
            PDO::PARAM_INT
        );

        $stmt->bindValue(
            ":company_name",
            $company_name,
            PDO::PARAM_STR
        );

        $stmt->bindValue(
            ":industry",
            $industry,
            PDO::PARAM_STR
        );

        $stmt->bindValue(
            ":job_type",
            $job_type,
            PDO::PARAM_STR
        );

        $stmt->bindValue(
            ":application_route",
            $application_route,
            PDO::PARAM_STR
        );

        $stmt->bindValue(
            ":service_name",
            $service_name,
            PDO::PARAM_STR
        );

        $stmt->bindValue(
            ":login_url",
            $login_url,
            PDO::PARAM_STR
        );

        $stmt->bindValue(
            ":login_email",
            $login_email,
            PDO::PARAM_STR
        );

        $stmt->bindValue(
            ":login_id",
            $login_id,
            PDO::PARAM_STR
        );

        $stmt->bindValue(
            ":password_management",
            $password_management,
            PDO::PARAM_STR
        );

        $stmt->bindValue(
            ":interest_level",
            (int)$interest_level,
            PDO::PARAM_INT
        );

        $stmt->bindValue(
            ":status",
            $status,
            PDO::PARAM_STR
        );

        $stmt->bindValue(
            ":memo",
            $memo,
            PDO::PARAM_STR
        );

        $stmt->bindValue(
            ":id",
            (int)$id,
            PDO::PARAM_INT
        );

        $stmt->execute();

        $message = "企業情報を更新しました。";


        // 更新後の情報を再取得
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
        
        $company = $stmt->fetch();

    } else {

        $message = "企業名を入力してください。";
    }
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

    <title>企業編集 | Job Compass</title>
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

            <a
                href="index.php#companies"
                class="active"
            >
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
                    企業情報を編集
                </h1>

                <div class="page-description">
                    登録済みの企業情報を変更します
                </div>

            </div>

        </header>


        <!-- =========================
             更新メッセージ
        ========================== -->
        <?php if ($message !== ""): ?>

            <div class="alert alert-success">
                <?= h($message) ?>
            </div>

        <?php endif; ?>


        <!-- =========================
             編集フォーム
        ========================== -->
        <div class="form-card">

            <div class="form-card-header">

                <h2 class="section-title">
                    企業情報
                </h2>

                <p class="section-description">
                    変更したい項目を編集してください
                </p>

            </div>


            <form
                method="POST"
                class="entry-form"
            >

                <!-- =====================
                     基本情報
                ====================== -->
                <div class="form-section">

                    <h3 class="form-section-title">
                        基本情報
                    </h3>


                    <!-- 企業名・業界 -->
                    <div class="form-row">

                        <div class="form-group">

                            <label for="company_name">
                                企業名
                            </label>

                            <input
                                type="text"
                                id="company_name"
                                name="company_name"
                                value="<?= h($company["company_name"]) ?>"
                                required
                            >

                        </div>


                        <div class="form-group">

                            <label for="industry">
                                業界
                            </label>

                            <input
                                type="text"
                                id="industry"
                                name="industry"
                                value="<?= h($company["industry"]) ?>"
                            >

                        </div>

                    </div>


                    <!-- 希望職種・応募経路 -->
                    <div class="form-row">

                        <div class="form-group">

                            <label for="job_type">
                                希望職種
                            </label>

                            <input
                                type="text"
                                id="job_type"
                                name="job_type"
                                value="<?= h($company["job_type"]) ?>"
                            >

                        </div>


                        <div class="form-group">

                            <label for="application_route">
                                応募経路
                            </label>

                            <input
                                type="text"
                                id="application_route"
                                name="application_route"
                                value="<?= h($company["application_route"]) ?>"
                            >

                        </div>

                    </div>


                    <!-- 利用サービス -->
                    <div class="form-group">

                        <label for="service_name">
                            利用した就活サービス
                        </label>

                        <input
                            type="text"
                            id="service_name"
                            name="service_name"
                            value="<?= h($company["service_name"]) ?>"
                        >

                    </div>

                </div>


                <!-- =====================
                     マイページ・ログイン
                ====================== -->
                <div class="form-section">

                    <h3 class="form-section-title">
                        マイページ・ログイン情報
                    </h3>


                    <div class="form-group">

                        <label for="login_url">
                            マイページURL
                        </label>

                        <input
                            type="url"
                            id="login_url"
                            name="login_url"
                            value="<?= h($company["login_url"]) ?>"
                            placeholder="https://..."
                        >

                    </div>


                    <!-- メール・ログインID -->
                    <div class="form-row">

                        <div class="form-group">

                            <label for="login_email">
                                登録メールアドレス
                            </label>

                            <input
                                type="email"
                                id="login_email"
                                name="login_email"
                                value="<?= h($company["login_email"]) ?>"
                            >

                        </div>


                        <div class="form-group">

                            <label for="login_id">
                                ログインID
                            </label>

                            <input
                                type="text"
                                id="login_id"
                                name="login_id"
                                value="<?= h($company["login_id"]) ?>"
                            >

                        </div>

                    </div>


                    <div class="form-group">

                        <label for="password_management">
                            パスワード管理方法
                        </label>

                        <input
                            type="text"
                            id="password_management"
                            name="password_management"
                            value="<?= h($company["password_management"]) ?>"
                            placeholder="例：ブラウザに保存"
                        >

                    </div>

                </div>


                <!-- =====================
                     就活管理情報
                ====================== -->
                <div class="form-section">

                    <h3 class="form-section-title">
                        就活管理情報
                    </h3>


                    <!-- 志望度・選考状況 -->
                    <div class="form-row">

                        <div class="form-group">

                            <label for="interest_level">
                                志望度
                            </label>

                            <select
                                id="interest_level"
                                name="interest_level"
                            >

                                <?php for ($i = 1; $i <= 5; $i++): ?>

                                    <option
                                        value="<?= $i ?>"
                                        <?php
                                        if (
                                            (int)$company["interest_level"]
                                            === $i
                                        ) {
                                            echo "selected";
                                        }
                                        ?>
                                    >
                                        <?= $i ?>
                                    </option>

                                <?php endfor; ?>

                            </select>

                        </div>


                        <div class="form-group">

                            <label for="status">
                                選考状況
                            </label>

                            <select
                                id="status"
                                name="status"
                            >

                                <?php

                                $statuses = [
                                    "検討中",
                                    "応募予定",
                                    "応募済み",
                                    "ES提出済み",
                                    "適性検査",
                                    "面接予定",
                                    "選考中",
                                    "内定",
                                    "不合格",
                                    "辞退"
                                ];

                                ?>

                                <?php foreach ($statuses as $status): ?>

                                    <option
                                        value="<?= h($status) ?>"
                                        <?php
                                        if (
                                            $company["status"]
                                            === $status
                                        ) {
                                            echo "selected";
                                        }
                                        ?>
                                    >
                                        <?= h($status) ?>
                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>

                    </div>


                    <!-- 備考 -->
                    <div class="form-group">

                        <label for="memo">
                            備考
                        </label>

                        <textarea
                            id="memo"
                            name="memo"
                            placeholder="企業について気になったこと、確認事項など"
                        ><?= h($company["memo"]) ?></textarea>

                    </div>

                </div>


                <!-- =====================
                     ボタン
                ====================== -->
                <div class="form-actions">

                    <a
                        href="index.php"
                        class="btn btn-secondary"
                    >
                        キャンセル
                    </a>

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        変更を保存
                    </button>

                </div>

            </form>

        </div>

    </main>

</div>

</body>
</html>