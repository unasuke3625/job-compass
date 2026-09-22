<?php

require_once __DIR__ . "/includes/auth.php";
require_once __DIR__ . "/includes/db.php";
require_once __DIR__ . "/includes/functions.php";

$user_id = (int)$_SESSION["user_id"];

$sql = "
    SELECT *
    FROM companies
    WHERE user_id = :user_id
    ORDER BY interest_level ASC, id DESC
";

$stmt = $pdo->prepare($sql);

$stmt->bindValue(
    ":user_id",
    $user_id,
    PDO::PARAM_INT
);

$stmt->execute();

$companies = $stmt->fetchAll(PDO::FETCH_ASSOC);

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

    <title>ダッシュボード | Job Compass</title>
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

            <a
                href="index.php"
                class="active"
            >
                ダッシュボード
            </a>

            <a href="selection_list.php">
                選考管理
            </a>

            <a href="schedule.php">
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
                    ダッシュボード
                </h1>

                <div class="page-description">
                    就職活動の状況を確認・管理できます
                </div>

            </div>

        </header>

        <!-- =========================
             ショートカット
        ========================== -->
        <div class="dashboard-actions">

            <a
                href="selection_create.php"
                class="action-card"
            >

                <div class="action-icon">
                    ＋
                </div>

                <div>

                    <div class="action-title">
                        選考予定を登録
                    </div>

                    <div class="action-description">
                        面接やES締切などを追加します
                    </div>

                </div>

            </a>


            <a
                href="selection_list.php"
                class="action-card"
            >

                <div class="action-icon">
                    →
                </div>

                <div>

                    <div class="action-title">
                        選考一覧を見る
                    </div>

                    <div class="action-description">
                        登録した選考状況を確認します
                    </div>

                </div>

            </a>

        </div>


        <!-- =========================
             企業一覧
        ========================== -->
        <div
            class="card"
            id="companies"
        >

            <div class="list-header">

                <div>

                    <h2 class="section-title">
                        企業一覧
                    </h2>

                    <p class="section-description">
                        登録した企業の情報を管理できます
                    </p>

                </div>


                <a
                    href="company_create.php"
                    class="btn btn-primary"
                >
                    ＋ 企業を登録
                </a>

            </div>


            <?php if (count($companies) === 0): ?>

                <p>
                    登録されている企業はありません。
                </p>

            <?php else: ?>
            
                <div class="table-wrapper">
            
                    <table>
            
                        <thead>
            
                            <tr>
            
                                <th>企業名</th>
            
                                <th>業界</th>
            
                                <th>希望職種</th>
            
                                <th>志望度</th>
            
                                <th>選考状況</th>
            
                                <th>マイページ</th>
            
                                <th>操作</th>
            
                            </tr>
            
                        </thead>
            
            
                        <tbody>
            
                            <?php foreach ($companies as $company): ?>
            
                                <tr>
            
                                    <td>
                                        <?= h($company["company_name"]) ?>
                                    </td>
            
                                    <td>
                                        <?= h($company["industry"]) ?>
                                    </td>
            
                                    <td>
                                        <?= h($company["job_type"]) ?>
                                    </td>
            
                                    <td>
                                        <?= h($company["interest_level"]) ?>
                                    </td>
            
                                    <td>
                                        <?= h($company["status"]) ?>
                                    </td>
            
                                    <td>
            
                                        <?php if (
                                            $company["login_url"] !== null &&
                                            $company["login_url"] !== ""
                                        ): ?>
            
                                            <a
                                                href="<?= h($company["login_url"]) ?>"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                            >
                                                マイページを開く
                                            </a>
            
                                        <?php else: ?>
            
                                            未登録
            
                                        <?php endif; ?>
            
                                    </td>
            
            
                                    <td>
            
                                        <a
                                            href="company_edit.php?id=<?= (int)$company["id"] ?>"
                                        >
                                            編集
                                        </a>
            
                                        <span class="separator">
                                            |
                                        </span>
            
                                        <a
                                            href="company_delete.php?id=<?= (int)$company["id"] ?>"
                                        >
                                            削除
                                        </a>
            
                                    </td>
            
                                </tr>
            
                            <?php endforeach; ?>
            
                        </tbody>
            
                    </table>
            
                </div>
            
            <?php endif; ?>


        </div>

    </main>

</div>

</body>

</html>
