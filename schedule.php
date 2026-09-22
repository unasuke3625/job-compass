<?php
require_once __DIR__ . "/includes/auth.php";
require_once __DIR__ . "/includes/db.php";
require_once __DIR__ . "/includes/functions.php";

$today = new DateTimeImmutable("today", new DateTimeZone("Asia/Tokyo"));
$month = $_GET["month"] ?? $today->format("Y-m");
if (!is_string($month) || !preg_match('/\A[1-9][0-9]{3}-(0[1-9]|1[0-2])\z/', $month)) {
    $month = $today->format("Y-m");
}
$first_day = new DateTimeImmutable($month . "-01", new DateTimeZone("Asia/Tokyo"));
$last_day = $first_day->modify("last day of this month");

// 選考管理と同じデータを参照し、登録・変更・削除を表示時に反映する。
$stmt = $pdo->prepare("
    SELECT selections.*, companies.company_name
    FROM selections
    INNER JOIN companies ON selections.company_id = companies.id
    WHERE companies.user_id = :user_id
      AND ((event_date BETWEEN :event_start AND :event_end)
        OR (deadline BETWEEN :deadline_start AND :deadline_end))
");
$stmt->bindValue(":user_id", (int)$_SESSION["user_id"], PDO::PARAM_INT);
$stmt->bindValue(":event_start", $first_day->format("Y-m-d"));
$stmt->bindValue(":event_end", $last_day->format("Y-m-d"));
$stmt->bindValue(":deadline_start", $first_day->format("Y-m-d"));
$stmt->bindValue(":deadline_end", $last_day->format("Y-m-d"));
$stmt->execute();

$events = [];
$event_count = 0;
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $selection) {
    foreach (["deadline" => "締切", "event_date" => "実施"] as $field => $label) {
        $date = $selection[$field];
        if (!$date || $date < $first_day->format("Y-m-d") || $date > $last_day->format("Y-m-d")) {
            continue;
        }
        $events[$date][] = array_merge($selection, [
            "kind" => $field,
            "label" => $label,
            "time" => $field === "event_date" ? substr($selection["start_time"] ?? "", 0, 5) : "",
        ]);
        $event_count++;
    }
}
foreach ($events as &$day_events) {
    usort($day_events, function ($a, $b) {
        return [$a["time"], $a["id"], $a["kind"]] <=> [$b["time"], $b["id"], $b["kind"]];
    });
}
unset($day_events);
$offset = (int)$first_day->format("w");
$days_in_month = (int)$last_day->format("j");
$cell_count = (int)(ceil(($offset + $days_in_month) / 7) * 7);
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>スケジュール | Job Compass</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .schedule-toolbar { display: flex; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 20px; }
        .schedule-toolbar h2 { margin: 0; }
        .schedule-scroll { overflow-x: auto; background: var(--color-surface); border-radius: 14px; }
        .schedule-calendar { width: 100%; min-width: 770px; table-layout: fixed; border-collapse: collapse; }
        .schedule-calendar th, .schedule-calendar td { border: 1px solid var(--color-border); padding: 8px; }
        .schedule-calendar th { background: var(--color-primary-light); }
        .schedule-calendar td { height: 140px; vertical-align: top; }
        .schedule-calendar .schedule-blank { background: #f5f7fb; }
        .schedule-calendar .schedule-today { background: #f0f6ff; }
        .schedule-date { font-weight: 700; }
        .schedule-today .schedule-date { color: #245bc0; }
        .schedule-event { display: block; margin-top: 8px; padding: 8px; border-left: 3px solid #3b73e8; border-radius: 4px; background: #edf3ff; font-size: 12px; overflow-wrap: anywhere; }
        .schedule-event.deadline { border-color: #b75c13; background: #fff4e5; }
        .schedule-event:hover { text-decoration: underline; }
        .schedule-event:focus-visible { outline: 2px solid #245bc0; }
        .schedule-event strong, .schedule-event span { display: block; }
        .schedule-note { color: #586174; font-size: 14px; }
    </style>
</head>
<body>
<div class="app-layout">
    <aside class="sidebar">
        <div class="sidebar-logo">Job<span>Compass</span></div>
        <nav class="sidebar-nav">
            <a href="index.php">ダッシュボード</a>
            <a href="selection_list.php">選考管理</a>
            <a href="schedule.php" class="active" aria-current="page">スケジュール</a>
            <a href="logout.php">ログアウト</a>
        </nav>
    </aside>
    <main class="main-area">
        <header class="page-header">
            <div>
                <h1 class="page-title">スケジュール</h1>
                <p class="page-description">選考管理で登録した実施日・締切日を確認できます。</p>
            </div>
            <a href="selection_create.php" class="btn btn-primary">予定を登録</a>
        </header>
        <nav class="schedule-toolbar" aria-label="表示月の切り替え">
            <a class="btn btn-secondary" href="?month=<?php echo h($first_day->modify('-1 month')->format('Y-m')); ?>">前月</a>
            <h2><?php echo h($first_day->format("Y年n月")); ?></h2>
            <a class="btn btn-secondary" href="?month=<?php echo h($first_day->modify('+1 month')->format('Y-m')); ?>">翌月</a>
            <a class="btn btn-secondary" href="schedule.php">今月</a>
        </nav>
        <p class="schedule-note">青：実施日 ／ オレンジ：締切日。予定をクリックすると編集できます。日付未設定の選考は表示されません。</p>
        <?php if ($event_count === 0): ?>
            <p role="status">この月に登録されている予定はありません。</p>
        <?php endif; ?>
        <div class="schedule-scroll" role="region" aria-label="月別スケジュール" tabindex="0">
            <table class="schedule-calendar" aria-label="<?php echo h($first_day->format('Y年n月')); ?>の予定">
                <thead><tr>
                    <?php foreach (["日", "月", "火", "水", "木", "金", "土"] as $weekday): ?>
                        <th scope="col"><?php echo h($weekday); ?></th>
                    <?php endforeach; ?>
                </tr></thead>
                <tbody>
                <?php for ($cell = 0; $cell < $cell_count; $cell++): ?>
                    <?php if ($cell % 7 === 0): ?><tr><?php endif; ?>
                    <?php $day = $cell - $offset + 1; ?>
                    <?php if ($day < 1 || $day > $days_in_month): ?>
                        <td class="schedule-blank"></td>
                    <?php else: ?>
                        <?php $date = sprintf("%s-%02d", $month, $day); ?>
                        <td class="<?php echo $date === $today->format('Y-m-d') ? 'schedule-today' : ''; ?>">
                            <time class="schedule-date" datetime="<?php echo h($date); ?>"><?php echo $day; ?><?php echo $date === $today->format('Y-m-d') ? '（今日）' : ''; ?></time>
                            <?php foreach ($events[$date] ?? [] as $event): ?>
                                <a class="schedule-event <?php echo h($event['kind']); ?>" href="selection_edit.php?id=<?php echo (int)$event['id']; ?>">
                                    <span><?php echo h($event["label"] . ($event["time"] !== "" ? " " . $event["time"] : "")); ?></span>
                                    <strong><?php echo h($event["company_name"]); ?></strong>
                                    <span><?php echo h($event["selection_type"]); ?>（<?php echo h($event["status"]); ?>）</span>
                                    <?php if ($event["kind"] === "event_date"): ?>
                                        <span><?php echo h($event["location_type"]); ?></span>
                                        <span><?php echo h($event["location"]); ?></span>
                                    <?php endif; ?>
                                </a>
                            <?php endforeach; ?>
                        </td>
                    <?php endif; ?>
                    <?php if ($cell % 7 === 6): ?></tr><?php endif; ?>
                <?php endfor; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>
</body>
</html>
