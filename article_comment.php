<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';

require_login();

$user      = current_user();
$articleId = (int)($_POST['article_id'] ?? 0);
$content   = trim($_POST['content'] ?? '');
$back      = $_POST['back'] ?? 'index.php';

if ($articleId <= 0 || $content === '') {
    echo '参数错误<br><a href="index.php">返回</a>';
    exit;
}
if (mb_strlen($content, 'UTF-8') > 500) {
    echo '留言最多 500 字<br><a href="' . htmlspecialchars($back) . '">返回</a>';
    exit;
}

$stmt = mysqli_prepare($connect,
    'INSERT INTO article_comments (article_id, user_id, content) VALUES (?, ?, ?)');
mysqli_stmt_bind_param($stmt, 'iis', $articleId, $user['id'], $content);
mysqli_stmt_execute($stmt);

header('Location: ' . $back);
exit;