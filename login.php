<?php

session_start();

require_once __DIR__ . "/includes/db.php";
require_once __DIR__ . "/includes/functions.php";


$error = "";


// すでにログインしている場合
if (isset($_SESSION["user_id"])) {

    header("Location: index.php");
    exit;
}


// ------------------------------------
// ログインフォームが送信された場合
// ------------------------------------

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";


    // ------------------------------------
    // 入力チェック
    // ------------------------------------

    if ($email === "" || $password === "") {

        $error =
            "メールアドレスとパスワードを入力してください。";

    } elseif (
        !filter_var(
            $email,
            FILTER_VALIDATE_EMAIL
        )
    ) {

        $error =
            "正しいメールアドレスを入力してください。";

    }


    // ------------------------------------
    // ユーザーを検索
    // ------------------------------------

    if ($error === "") {

        $sql = "
            SELECT
                id,
                name,
                email,
                password_hash
            FROM users
            WHERE email = :email
        ";

        $stmt = $pdo->prepare($sql);

        $stmt->bindValue(
            ":email",
            $email,
            PDO::PARAM_STR
        );

        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);


        // ------------------------------------
        // パスワード確認
        // ------------------------------------

        if (
            !$user ||
            !password_verify(
                $password,
                $user["password_hash"]
            )
        ) {

            $error =
                "メールアドレスまたはパスワードが正しくありません。";

        } else {

            // セッションIDを更新
            session_regenerate_id(true);


            // ログイン情報を保存
            $_SESSION["user_id"] =
                (int)$user["id"];

            $_SESSION["user_name"] =
                $user["name"];


            // ダッシュボードへ移動
            header("Location: index.php");
            exit;
        }

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

    <link
        rel="stylesheet"
        href="css/style.css"
    >

    <title>
        ログイン | Job Compass
    </title>

</head>


<body>

<div class="auth-page">

    <div class="auth-container">

        <div class="auth-logo">
            Job<span>Compass</span>
        </div>


        <div class="auth-card">

            <div class="auth-header">

                <h1>
                    ログイン
                </h1>

                <p>
                    Job Compassへログインします
                </p>

            </div>


            <?php if ($error !== ""): ?>

                <div class="alert alert-error">

                    <?= h($error) ?>

                </div>

            <?php endif; ?>


            <form
                method="POST"
                class="entry-form"
                autocomplete="off"
            >

                <div class="form-group">

                    <label for="email">
                        メールアドレス
                    </label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="example@email.com"
                        autocomplete="off"
                        required
                    >

                </div>


                <div class="form-group">

                    <label for="password">
                        パスワード
                    </label>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="パスワードを入力"
                        autocomplete="off"
                        required
                    >

                </div>


                <button
                    type="submit"
                    class="btn btn-primary auth-button"
                >
                    ログイン
                </button>

            </form>


            <div class="auth-footer">

                アカウントをお持ちでないですか？

                <a href="register.php">
                    アカウントを作成
                </a>

            </div>

        </div>

    </div>

</div>

</body>

</html>