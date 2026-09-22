<?php

require_once __DIR__ . "/includes/auth.php";
require_once __DIR__ . "/includes/db.php";
require_once __DIR__ . "/includes/functions.php";

$user_id = (int)$_SESSION["user_id"];

// ------------------------------------
// 1. URLから選考IDを取得
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
// 2. 更新ボタンが押された場合
// ------------------------------------

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    
    $company_id = $_POST["company_id"] ?? "";
    
    if (
        $company_id !== "" &&
        !ctype_digit($company_id)
    ) {
        $error = "企業を正しく選択してください。";
    }
    
    if (
        $error === "" &&
        $company_id !== ""
    ) {
    
        $sql = "
            SELECT id
            FROM companies
            WHERE id = :company_id
            AND user_id = :user_id
        ";
    
        $stmt = $pdo->prepare($sql);
    
        $stmt->bindValue(
            ":company_id",
            (int)$company_id,
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
            $error = "選択された企業を利用できません。";
        }
    }
    
    $company_id = (int)$company_id;

    $selection_type = trim($_POST["selection_type"] ?? "");
    $deadline = trim($_POST["deadline"] ?? "");
    $event_date = trim($_POST["event_date"] ?? "");
    $start_time = trim($_POST["start_time"] ?? "");
    $location_type = trim($_POST["location_type"] ?? "");
    $location = trim($_POST["location"] ?? "");
    $status = trim($_POST["status"] ?? "");
    $memo = trim($_POST["memo"] ?? "");


    // 選考種別は必須
    if ($selection_type === "") {
        exit("選考種別を入力してください。");
    }


    $sql = "
        UPDATE selections
        SET
            company_id = :company_id,
            selection_type = :selection_type,
            deadline = :deadline,
            event_date = :event_date,
            start_time = :start_time,
            location_type = :location_type,
            location = :location,
            status = :status,
            memo = :memo
        WHERE id = :id
        AND company_id IN (
            SELECT id
            FROM companies
            WHERE user_id = :user_id
        )
    ";

    $stmt = $pdo->prepare($sql);
    
    $stmt->bindValue(
        ":user_id",
        $user_id,
        PDO::PARAM_INT
    );
    
    $stmt->bindValue(
        ":company_id",
        $company_id,
        PDO::PARAM_INT
    );

    $stmt->bindValue(
        ":selection_type",
        $selection_type,
        PDO::PARAM_STR
    );

    $stmt->bindValue(
        ":deadline",
        $deadline !== "" ? $deadline : null,
        $deadline !== "" ? PDO::PARAM_STR : PDO::PARAM_NULL
    );

    $stmt->bindValue(
        ":event_date",
        $event_date !== "" ? $event_date : null,
        $event_date !== "" ? PDO::PARAM_STR : PDO::PARAM_NULL
    );

    $stmt->bindValue(
        ":start_time",
        $start_time !== "" ? $start_time : null,
        $start_time !== "" ? PDO::PARAM_STR : PDO::PARAM_NULL
    );

    $stmt->bindValue(
        ":location_type",
        $location_type,
        PDO::PARAM_STR
    );

    $stmt->bindValue(
        ":location",
        $location,
        PDO::PARAM_STR
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
        $selection_id,
        PDO::PARAM_INT
    );

    $stmt->execute();


    // 更新後、一覧画面に戻る
        header("Location: selection_list.php");
        exit;
    }


    // ------------------------------------
    // 3. 現在の選考データを取得
    // ------------------------------------
    
    $sql = "
        SELECT
            selections.*
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
    
    if (!$selection) {
        exit("選考情報が見つかりません。");
    }
    
    // 企業一覧を取得
    $sql = "
        SELECT id, company_name
        FROM companies
        WHERE user_id = :user_id
        ORDER BY company_name
    ";
    
    $stmt = $pdo->prepare($sql);
    
    $stmt->bindValue(
        ":user_id",
        $user_id,
        PDO::PARAM_INT
    );
    
    $stmt->execute();
    
    $companies = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $selection_statuses = getSelectionStatuses();
    $selection_types = getSelectionTypes();

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

    <title>選考情報編集 | Job Compass</title>
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
                    選考情報を編集
                </h1>

                <div class="page-description">
                    登録済みの選考情報を変更します
                </div>

            </div>

        </header>


        <!-- =========================
             編集フォーム
        ========================== -->
        <div class="form-card">

            <div class="form-card-header">

                <h2 class="section-title">
                    選考情報
                </h2>

                <p class="section-description">
                    変更したい項目を編集してください
                </p>

            </div>


            <form
                method="post"
                class="entry-form"
            >

                <!-- 企業・選考種別 -->
                <div class="form-row">

                    <div class="form-group">

                        <label for="company_id">
                            企業
                        </label>

                        <select
                            id="company_id"
                            name="company_id"
                            required
                        >

                            <?php foreach ($companies as $company): ?>

                                <option
                                    value="<?php echo (int)$company["id"]; ?>"
                                    <?php
                                    if (
                                        (int)$company["id"] ===
                                        (int)$selection["company_id"]
                                    ) {
                                        echo "selected";
                                    }
                                    ?>
                                >
                                    <?php echo htmlspecialchars(
                                        $company["company_name"],
                                        ENT_QUOTES,
                                        "UTF-8"
                                    ); ?>
                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>


                    <div class="form-group">

                        <label for="selection_type">
                            選考種別
                        </label>
                    
                        <select
                            id="selection_type"
                            name="selection_type"
                            required
                        >
                    
                            <?php foreach ($selection_types as $selection_type): ?>
                    
                                <option
                                    value="<?php echo htmlspecialchars(
                                        $selection_type,
                                        ENT_QUOTES,
                                        "UTF-8"
                                    ); ?>"
                                    <?php
                                    if (
                                        $selection["selection_type"]
                                        === $selection_type
                                    ) {
                                        echo "selected";
                                    }
                                    ?>
                                >
                                    <?php echo htmlspecialchars(
                                        $selection_type,
                                        ENT_QUOTES,
                                        "UTF-8"
                                    ); ?>
                                </option>
                    
                            <?php endforeach; ?>
                    
                        </select>
                    
                    </div>

                </div>


                <!-- 締切日・実施日 -->
                <div class="form-row">

                    <div class="form-group">

                        <label for="deadline">
                            締切日
                        </label>

                        <input
                            type="date"
                            id="deadline"
                            name="deadline"
                            value="<?php echo htmlspecialchars(
                                $selection["deadline"] ?? "",
                                ENT_QUOTES,
                                "UTF-8"
                            ); ?>"
                        >

                    </div>


                    <div class="form-group">

                        <label for="event_date">
                            実施日
                        </label>

                        <input
                            type="date"
                            id="event_date"
                            name="event_date"
                            value="<?php echo htmlspecialchars(
                                $selection["event_date"] ?? "",
                                ENT_QUOTES,
                                "UTF-8"
                            ); ?>"
                        >

                    </div>

                </div>


                <!-- 開始時刻・実施形式 -->
                <div class="form-row">

                    <div class="form-group">

                        <label for="start_time">
                            開始時刻
                        </label>

                        <input
                            type="time"
                            id="start_time"
                            name="start_time"
                            value="<?php echo htmlspecialchars(
                                $selection["start_time"] ?? "",
                                ENT_QUOTES,
                                "UTF-8"
                            ); ?>"
                        >

                    </div>


                    <div class="form-group">

                        <label for="location_type">
                            実施形式
                        </label>

                        <select
                            id="location_type"
                            name="location_type"
                        >

                            <option
                                value=""
                                <?php
                                if (
                                    ($selection["location_type"] ?? "") === ""
                                ) {
                                    echo "selected";
                                }
                                ?>
                            >
                                選択してください
                            </option>

                            <option
                                value="オンライン"
                                <?php
                                if (
                                    $selection["location_type"] === "オンライン"
                                ) {
                                    echo "selected";
                                }
                                ?>
                            >
                                オンライン
                            </option>

                            <option
                                value="対面"
                                <?php
                                if (
                                    $selection["location_type"] === "対面"
                                ) {
                                    echo "selected";
                                }
                                ?>
                            >
                                対面
                            </option>

                            <option
                                value="ハイブリッド"
                                <?php
                                if (
                                    $selection["location_type"] === "ハイブリッド"
                                ) {
                                    echo "selected";
                                }
                                ?>
                            >
                                ハイブリッド
                            </option>

                            <option
                                value="その他"
                                <?php
                                if (
                                    $selection["location_type"] === "その他"
                                ) {
                                    echo "selected";
                                }
                                ?>
                            >
                                その他
                            </option>

                        </select>

                    </div>

                </div>


                <!-- 場所 -->
                <div class="form-group">

                    <label for="location">
                        場所・URL等
                    </label>

                    <input
                        type="text"
                        id="location"
                        name="location"
                        value="<?php echo htmlspecialchars(
                            $selection["location"] ?? "",
                            ENT_QUOTES,
                            "UTF-8"
                        ); ?>"
                        placeholder="例：東京本社、Zoom URL"
                    >

                </div>


                <!-- ステータス -->
                <div class="form-group">

                    <label for="status">
                        ステータス
                    </label>

                    <select
                        id="status"
                        name="status"
                    >
                    
                        <?php foreach ($selection_statuses as $selection_status): ?>
                    
                            <option
                                value="<?php echo htmlspecialchars(
                                    $selection_status,
                                    ENT_QUOTES,
                                    "UTF-8"
                                ); ?>"
                                <?php
                                if (
                                    $selection["status"] === $selection_status
                                ) {
                                    echo "selected";
                                }
                                ?>
                            >
                                <?php echo htmlspecialchars(
                                    $selection_status,
                                    ENT_QUOTES,
                                    "UTF-8"
                                ); ?>
                            </option>
                    
                        <?php endforeach; ?>
                    
                    </select>

                </div>


                <!-- メモ -->
                <div class="form-group">

                    <label for="memo">
                        メモ
                    </label>

                    <textarea
                        id="memo"
                        name="memo"
                        placeholder="面接内容、注意事項、次回確認したいことなど"
                    ><?php echo htmlspecialchars(
                        $selection["memo"] ?? "",
                        ENT_QUOTES,
                        "UTF-8"
                    ); ?></textarea>

                </div>


                <!-- ボタン -->
                <div class="form-actions">

                    <a
                        href="selection_list.php"
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