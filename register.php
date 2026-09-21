<?php

require_once __DIR__ . "/includes/db.php";
require_once __DIR__ . "/includes/functions.php";

$error = "";
$message = "";


// ------------------------------------
// 登録フォームが送信された場合
// ------------------------------------

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";
    $password_confirm = $_POST["password_confirm"] ?? "";


    // ------------------------------------
    // 入力チェック
    // ------------------------------------

    if (
        $name === "" ||
        $email === "" ||
        $password === "" ||
        $password_confirm === ""
    ) {

        $error = "すべての項目を入力してください。";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = "正しいメールアドレスを入力してください。";

    } elseif ($password !== $password_confirm) {

        $error = "パスワードが一致していません。";

    } elseif (strlen($password) < 8) {

        $error = "パスワードは8文字以上で入力してください。";

    }


    // ------------------------------------
    // メールアドレスの重複確認
    // ------------------------------------

    if ($error === "") {

        $sql = "
            SELECT id
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

        $existing_user = $stmt->fetch(PDO::FETCH_ASSOC);


        if ($existing_user) {

            $error =
                "このメールアドレスはすでに登録されています。";
        }

    }


    // ------------------------------------
    // ユーザー登録
    // ------------------------------------

    if ($error === "") {

        $password_hash = password_hash(
            $password,
            PASSWORD_DEFAULT
        );


        $sql = "
            INSERT INTO users (
                name,
                email,
                password_hash
            )
            VALUES (
                :name,
                :email,
                :password_hash
            )
        ";

        $stmt = $pdo->prepare($sql);


        $stmt->bindValue(
            ":name",
            $name,
            PDO::PARAM_STR
        );


        $stmt->bindValue(
            ":email",
            $email,
            PDO::PARAM_STR
        );


        $stmt->bindValue(
            ":password_hash",
            $password_hash,
            PDO::PARAM_STR
        );


        $stmt->execute();


        $message =
            "アカウントを作成しました。";

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
        アカウント作成 | Job Compass
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
                    アカウントを作成
                </h1>

                <p>
                    Job Compassを利用するための
                    アカウントを作成します
                </p>

            </div>


            <?php if ($error !== ""): ?>

                <div class="alert alert-error">

                    <?= h($error) ?>

                </div>

            <?php endif; ?>


            <?php if ($message !== ""): ?>

                <div class="alert alert-success">

                    <?= h($message) ?>

                </div>

            <?php endif; ?>


            <form
                method="POST"
                class="entry-form"
            >

                <div class="form-group">

                    <label for="name">
                        名前
                    </label>

                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="<?= h($_POST["name"] ?? "") ?>"
                        placeholder="例：就職 太郎"
                        required
                    >

                </div>


                <div class="form-group">

                    <label for="email">
                        メールアドレス
                    </label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="<?= h($_POST["email"] ?? "") ?>"
                        placeholder="example@email.com"
                        autocomplete="email"
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
                        placeholder="8文字以上"
                        autocomplete="new-password"
                        required
                    >

                </div>


                <div class="form-group">

                    <label for="password_confirm">
                        パスワード確認
                    </label>

                    <input
                        type="password"
                        id="password_confirm"
                        name="password_confirm"
                        placeholder="もう一度入力してください"
                        required
                    >

                </div>


                <button
                    type="submit"
                    class="btn btn-primary auth-button"
                >
                    アカウントを作成
                </button>

            </form>


            <div class="auth-footer">

                すでにアカウントをお持ちですか？

                <a href="login.php">
                    ログイン
                </a>

            </div>

        </div>

    </div>

</div>

</body>

</html>