<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

require_login();

$user    = current_user();
$content = trim($_POST['content'] ?? '');

if ($content === '') {
    echo '留言不能为空<br><a href="index.php">返回</a>';
    exit;
}
if (mb_strlen($content, 'UTF-8') > 500) {
    echo '留言最多 500 字<br><a href="index.php">返回</a>';
    exit;
}

insertMessage($connect, (int)$user['id'], $content);

header('Location: index.php');
exit;