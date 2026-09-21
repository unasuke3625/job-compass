<?php

require_once __DIR__ . "/includes/auth.php";
require_once __DIR__ . "/includes/db.php";
require_once __DIR__ . "/includes/functions.php";

$user_id = (int)$_SESSION["user_id"];

$company_id = trim($_GET["company_id"] ?? "");
$status = trim($_GET["status"] ?? "");


$sql = "
    SELECT
        selections.*,
        companies.company_name
    FROM selections
    INNER JOIN companies
        ON selections.company_id = companies.id
";

$conditions = [
    "companies.user_id = :user_id"
];

if ($company_id !== "") {

    if (!ctype_digit($company_id)) {
        exit("不正な企業IDです。");
    }

    $conditions[] = "selections.company_id = :company_id";
}


if ($status !== "") {
    $conditions[] = "selections.status = :status";
}


if (!empty($conditions)) {

    $sql .= "
        WHERE " . implode(" AND ", $conditions);
}


$sql .= "
    ORDER BY
        COALESCE(
            selections.deadline,
            selections.event_date
        ) IS NULL ASC,

        COALESCE(
            selections.deadline,
            selections.event_date
        ) ASC,

        selections.id DESC
";


$stmt = $pdo->prepare($sql);

$stmt->bindValue(
    ":user_id",
    $user_id,
    PDO::PARAM_INT
);


if ($company_id !== "") {

    $stmt->bindValue(
        ":company_id",
        (int)$company_id,
        PDO::PARAM_INT
    );
}


if ($status !== "") {

    $stmt->bindValue(
        ":status",
        $status,
        PDO::PARAM_STR
    );
}

$stmt->execute();

$selections = $stmt->fetchAll(PDO::FETCH_ASSOC);

$company_sql = "
    SELECT id, company_name
    FROM companies
    WHERE user_id = :user_id
    ORDER BY company_name ASC
";

$company_stmt = $pdo->prepare($company_sql);

$company_stmt->bindValue(
    ":user_id",
    $user_id,
    PDO::PARAM_INT
);

$company_stmt->execute();

$companies = $company_stmt->fetchAll(PDO::FETCH_ASSOC);

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

    <title>選考一覧 | Job Compass</title>
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

            <!-- ページタイトル -->
            <header class="page-header">

                <div>

                    <h1 class="page-title">
                        選考管理
                    </h1>

                    <div class="page-description">
                        応募企業と選考状況を管理します
                    </div>

                </div>

            </header>


            <!-- =========================
                 絞り込み
            ========================== -->
            <div class="card">
            
                <form method="get" class="filter-form">
            
                    <div class="filter-group">

                        <label for="company_id">
                            企業
                        </label>
                    
                        <select
                            id="company_id"
                            name="company_id"
                        >
                    
                            <option value="">
                                すべて
                            </option>
                    
                            <?php foreach ($companies as $company): ?>
                    
                                <option
                                    value="<?php echo (int)$company["id"]; ?>"
                                    <?php
                                    if (
                                        $company_id !== "" &&
                                        (int)$company_id === (int)$company["id"]
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
            
            
                    <div class="filter-group">
            
                        <label for="status">
                            選考状況
                        </label>
            
                        <select
                            id="status"
                            name="status"
                        >
                            <option value="">
                                すべて
                            </option>
            
                            <option
                                value="予定"
                                <?php if ($status === "予定") echo "selected"; ?>
                            >
                                予定
                            </option>
            
                            <option
                                value="選考中"
                                <?php if ($status === "選考中") echo "selected"; ?>
                            >
                                選考中
                            </option>
            
                            <option
                                value="結果待ち"
                                <?php if ($status === "結果待ち") echo "selected"; ?>
                            >
                                結果待ち
                            </option>
            
                            <option
                                value="通過"
                                <?php if ($status === "通過") echo "selected"; ?>
                            >
                                通過
                            </option>
            
                            <option
                                value="不合格"
                                <?php if ($status === "不合格") echo "selected"; ?>
                            >
                                不合格
                            </option>
            
                            <option
                                value="辞退"
                                <?php if ($status === "辞退") echo "selected"; ?>
                            >
                                辞退
                            </option>
            
                        </select>
            
                    </div>
            
            
                    <div class="filter-actions">
            
                        <button
                            type="submit"
                            class="btn btn-primary"
                        >
                            絞り込む
                        </button>
            
                        <a
                            href="selection_list.php"
                            class="btn btn-secondary"
                        >
                            解除
                        </a>
            
                    </div>
            
                </form>
            
            </div>


            <!-- =========================
                 選考一覧
            ========================== -->
            <div class="card selection-list-card">

                <div class="list-header">

                    <div>
                        <h2 class="section-title">
                            選考一覧
                        </h2>
                
                        <p class="section-description">
                            登録済みの選考予定を確認できます
                        </p>
                    </div>
                
                    <a
                        href="selection_create.php"
                        class="btn btn-primary"
                    >
                        ＋ 選考予定を登録
                    </a>
                
                </div>


                <?php if (empty($selections)): ?>

                    <p>
                        まだ選考予定が登録されていません。
                    </p>
                
                <?php else: ?>
                
                    <div class="table-wrapper">
                
                        <table>
                
                            <thead>
                
                                <tr>
                                    <th>企業名</th>
                                    <th>選考種別</th>
                                    <th>締切日</th>
                                    <th>実施日</th>
                                    <th>開始時刻</th>
                                    <th>実施形式</th>
                                    <th>場所</th>
                                    <th>選考状況</th>
                                    <th>メモ</th>
                                    <th>操作</th>
                                </tr>
                
                            </thead>
                
                            <tbody>
                
                                <?php foreach ($selections as $selection): ?>
                
                                    <tr>
                
                                        <td>
                                            <?= h($selection["company_name"]) ?>
                                        </td>
                
                                        <td>
                                            <?= h($selection["selection_type"]) ?>
                                        </td>
                
                                        <td>
                                            <?= h($selection["deadline"] ?? "") ?>
                                        </td>
                
                                        <td>
                                            <?= h($selection["event_date"] ?? "") ?>
                                        </td>
                
                                        <td>
                                            <?= h($selection["start_time"] ?? "") ?>
                                        </td>
                
                                        <td>
                                            <?= h($selection["location_type"] ?? "") ?>
                                        </td>
                
                                        <td>
                                            <?= h($selection["location"] ?? "") ?>
                                        </td>
                
                                        <td>
                                            <?= h($selection["status"] ?? "") ?>
                                        </td>
                
                                        <td>
                                            <?= h($selection["memo"] ?? "") ?>
                                        </td>
                
                                        <td>
                
                                            <a
                                                href="selection_edit.php?id=<?= (int)$selection["id"] ?>"
                                            >
                                                編集
                                            </a>
                
                                            <span class="separator">
                                                |
                                            </span>
                
                                            <a
                                                href="selection_delete.php?id=<?= (int)$selection["id"] ?>"
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