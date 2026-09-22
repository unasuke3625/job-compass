<?php

require_once __DIR__ . "/includes/auth.php";
require_once __DIR__ . "/includes/db.php";
require_once __DIR__ . "/includes/functions.php";

$user_id = (int)$_SESSION["user_id"];

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

$error = "";
$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $company_id = $_POST["company_id"] ?? "";
    $selection_type = $_POST["selection_type"] ?? "";
    $deadline = $_POST["deadline"] ?? "";
    $event_date = $_POST["event_date"] ?? "";
    $start_time = $_POST["start_time"] ?? "";
    $location_type = $_POST["location_type"] ?? "";
    $location = $_POST["location"] ?? "";
    $status = $_POST["status"] ?? "予定";
    $memo = $_POST["memo"] ?? "";


    // 必須項目チェック
    if ($company_id === "" || $selection_type === "") {

        $error = "企業と選考種別を入力してください.";

    }


    // ------------------------------------
    // 選択された企業が自分の企業か確認
    // ------------------------------------
    if (
        $error === "" &&
        $company_id !== "" &&
        ctype_digit($company_id)
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


    if ($error === "") {

        // 空欄の日付はNULLにする
        $deadline = $deadline !== ""
            ? $deadline
            : null;

        $event_date = $event_date !== ""
            ? $event_date
            : null;


        $sql = "
            INSERT INTO selections (
                company_id,
                selection_type,
                deadline,
                event_date,
                start_time,
                location_type,
                location,
                status,
                memo
            )
            VALUES (
                :company_id,
                :selection_type,
                :deadline,
                :event_date,
                :start_time,
                :location_type,
                :location,
                :status,
                :memo
            )
        ";

        $stmt = $pdo->prepare($sql);


        $stmt->bindValue(
            ":company_id",
            (int)$company_id,
            PDO::PARAM_INT
        );


        $stmt->bindValue(
            ":selection_type",
            $selection_type,
            PDO::PARAM_STR
        );


        $stmt->bindValue(
            ":deadline",
            $deadline,
            $deadline === null
                ? PDO::PARAM_NULL
                : PDO::PARAM_STR
        );


        $stmt->bindValue(
            ":event_date",
            $event_date,
            $event_date === null
                ? PDO::PARAM_NULL
                : PDO::PARAM_STR
        );
        
        $stmt->bindValue(
            ":start_time",
            $start_time,
            $start_time === null
                ? PDO::PARAM_NULL
                : PDO::PARAM_STR
        );
        
        $stmt->bindValue(
            ":location_type",
            $location_type,
            $location_type === null
                ? PDO::PARAM_NULL
                : PDO::PARAM_STR
        );
        
        $stmt->bindValue(
            ":location",
            $location,
            $location === null
                ? PDO::PARAM_NULL
                : PDO::PARAM_STR
        );
        
        $stmt->bindValue(
            ":status",
            $status,
            PDO::PARAM_STR
        );
        
        $stmt->bindValue(
            ":memo",
            $memo,
            $memo === null
                ? PDO::PARAM_NULL
                : PDO::PARAM_STR
        );


        $stmt->execute();

        $message = "選考予定を登録しました。";

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

    <title>選考予定登録 | Job Compass</title>
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

        <!-- ページタイトル -->
        <header class="page-header">

            <div>

                <h1 class="page-title">
                    選考予定を登録
                </h1>

                <div class="page-description">
                    面接やES締切など、新しい選考情報を登録します
                </div>

            </div>

        </header>


        <!-- =========================
             メッセージ
        ========================== -->

        <?php if ($error !== ""): ?>

            <div class="alert alert-error">

                <?php echo htmlspecialchars(
                    $error,
                    ENT_QUOTES,
                    "UTF-8"
                ); ?>

            </div>

        <?php endif; ?>


        <?php if ($message !== ""): ?>

            <div class="alert alert-success">

                <?php echo htmlspecialchars(
                    $message,
                    ENT_QUOTES,
                    "UTF-8"
                ); ?>

            </div>

        <?php endif; ?>


        <!-- =========================
             登録フォーム
        ========================== -->
        <div class="form-card">

            <div class="form-card-header">

                <h2 class="section-title">
                    選考情報
                </h2>

                <p class="section-description">
                    選考の日程や実施形式を入力してください
                </p>

            </div>


            <form
                method="POST"
                class="entry-form"
            >

                <!-- 企業・選考種別 -->
                <div class="form-row">

                    <div class="form-group">

                        <label for="company_id">
                            企業
                        </label>

                        <select
                            name="company_id"
                            id="company_id"
                            required
                        >

                            <option value="">
                                企業を選択してください
                            </option>

                            <?php foreach ($companies as $company): ?>

                                <option
                                    value="<?php echo (int)$company["id"]; ?>"
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
                            name="selection_type"
                            id="selection_type"
                            required
                        >

                            <?php foreach ($selection_types as $selection_type): ?>

                                <option
                                    value="<?php echo htmlspecialchars(
                                        $selection_type,
                                        ENT_QUOTES,
                                        "UTF-8"
                                    ); ?>"
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
                            name="deadline"
                            id="deadline"
                        >

                    </div>


                    <div class="form-group">

                        <label for="event_date">
                            実施日
                        </label>

                        <input
                            type="date"
                            name="event_date"
                            id="event_date"
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
                            name="start_time"
                            id="start_time"
                        >

                    </div>


                    <div class="form-group">

                        <label for="location_type">
                            実施形式
                        </label>

                        <select
                            name="location_type"
                            id="location_type"
                        >

                            <option value="">
                                選択してください
                            </option>

                            <option value="オンライン">
                                オンライン
                            </option>

                            <option value="対面">
                                対面
                            </option>

                            <option value="ハイブリッド">
                                ハイブリッド
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
                        name="location"
                        id="location"
                        placeholder="例：東京本社、Zoom URL"
                    >

                </div>


                <!-- ステータス -->
                <div class="form-group">

                    <label for="status">
                        ステータス
                    </label>

                    <select
                        name="status"
                        id="status"
                        required
                    >
                    
                        <?php foreach ($selection_statuses as $selection_status): ?>
                    
                            <option
                                value="<?php echo htmlspecialchars(
                                    $selection_status,
                                    ENT_QUOTES,
                                    "UTF-8"
                                ); ?>"
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
                        name="memo"
                        id="memo"
                        placeholder="持ち物、面接官、注意事項など"
                    ></textarea>

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
                        選考予定を登録
                    </button>

                </div>

            </form>

        </div>

    </main>

</div>

</body>
</html>