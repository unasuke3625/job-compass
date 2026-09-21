<?php
function h($value)
{
    return htmlspecialchars(
        $value ?? "",
        ENT_QUOTES,
        "UTF-8"
    );
}
    
function getSelectionStatuses(): array
{
    return [
        "予定",
        "選考中",
        "結果待ち",
        "通過",
        "不合格",
        "辞退"
    ];
}

function getSelectionTypes(): array
{
    return [
        "説明会",
        "ES",
        "Webテスト",
        "GD",
        "面接",
        "インターン",
        "その他"
    ];
}
?>