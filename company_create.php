<?php

require_once __DIR__ . "/includes/auth.php";
require_once __DIR__ . "/includes/db.php";
require_once __DIR__ . "/includes/functions.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $company_name = $_POST["company_name"];
    $industry = $_POST["industry"];
    $job_type = $_POST["job_type"];
    $application_route = $_POST["application_route"];
    $service_name = $_POST["service_name"];

    $login_url = $_POST["login_url"];
    $login_email = $_POST["login_email"];
    $login_id = $_POST["login_id"];
    // DBの旧カラム名は互換性のため維持。保存するのは管理方法のみ。
    $credential_note = $_POST["credential_note"] ?? "";
    if (!isValidCredentialNote($credential_note) || isset($_POST["password_management"])) {
        http_response_code(400);
        exit("ログイン管理方法を選択してください。実際のパスワードは保存できません。");
    }

    $interest_level = $_POST["interest_level"];
    $status = $_POST["status"];
    $memo = $_POST["memo"];
    
    $user_id = (int)$_SESSION["user_id"];

    if ($company_name !== "") {

        $sql = "
            INSERT INTO companies (
                user_id,
                company_name,
                industry,
                job_type,
                application_route,
                service_name,
                login_url,
                login_email,
                login_id,
                password_management,
                interest_level,
                status,
                memo
            )
            VALUES (
                :user_id,
                :company_name,
                :industry,
                :job_type,
                :application_route,
                :service_name,
                :login_url,
                :login_email,
                :login_id,
                :credential_note,
                :interest_level,
                :status,
                :memo
            )
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
            ":credential_note",
            $credential_note,
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

        $stmt->execute();

        echo "企業情報を登録しました。";
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

    <title>企業登録 | Job Compass</title>
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
                    企業を登録
                </h1>

                <div class="page-description">
                    応募を検討している企業の情報を登録します
                </div>

            </div>

        </header>


        <!-- 登録成功メッセージ -->
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
             企業登録フォーム
        ========================== -->
        <div class="form-card">

            <div class="form-card-header">

                <h2 class="section-title">
                    企業情報
                </h2>

                <p class="section-description">
                    企業や応募に関する情報を入力してください
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
                                placeholder="例：○○株式会社"
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
                                placeholder="例：IT・SIer"
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
                                placeholder="例：システムエンジニア"
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
                                placeholder="例：企業HP、就活サービス"
                            >

                        </div>

                    </div>


                    <!-- 就活サービス -->
                    <div class="form-group">

                        <label for="service_name">
                            利用した就活サービス
                        </label>

                        <input
                            type="text"
                            id="service_name"
                            name="service_name"
                            placeholder="例：○○ナビ、オファー系サービス"
                        >

                    </div>

                </div>


                <!-- =====================
                     ログイン情報
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
                                placeholder="example@email.com"
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
                            >

                        </div>

                    </div>


                    <div class="form-group">

                        <label for="credential_note">
                            ログイン管理方法
                        </label>

                        <select id="credential_note" name="credential_note" aria-describedby="credential_note_help">
                            <option value="">未設定</option>
                            <?php foreach (getCredentialNoteOptions() as $option): ?>
                                <option value="<?= h($option) ?>"><?= h($option) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <p id="credential_note_help" class="section-description">管理方法・登録方法のみ記録します。実際のパスワードや秘密の質問の答えは、備考欄を含め入力しないでください。</p>

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
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                                <option value="4">4</option>
                                <option value="5">5</option>
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
                                <option value="検討中">
                                    検討中
                                </option>

                                <option value="応募予定">
                                    応募予定
                                </option>

                                <option value="応募済み">
                                    応募済み
                                </option>

                                <option value="ES提出済み">
                                    ES提出済み
                                </option>

                                <option value="適性検査">
                                    適性検査
                                </option>

                                <option value="面接予定">
                                    面接予定
                                </option>

                                <option value="選考中">
                                    選考中
                                </option>

                                <option value="内定">
                                    内定
                                </option>

                                <option value="不合格">
                                    不合格
                                </option>

                                <option value="辞退">
                                    辞退
                                </option>

                            </select>

                        </div>

                    </div>


                    <div class="form-group">

                        <label for="memo">
                            備考
                        </label>

                        <textarea
                            id="memo"
                            name="memo"
                            placeholder="企業について気になったこと、確認事項など"
                        ></textarea>

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
                        企業を登録
                    </button>

                </div>

            </form>

        </div>

    </main>

</div>

</body>
</html>
