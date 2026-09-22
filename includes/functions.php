<?php
// 企業マイページの秘密情報は受け付けず、管理方法だけを保存する。
function getCredentialNoteOptions(): array
{
    return ["パスワードマネージャーに保存", "Googleアカウントで登録", "大学メールを使用"];
}

function isValidCredentialNote($value): bool
{
    return is_string($value) && ($value === "" || in_array($value, getCredentialNoteOptions(), true));
}

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
