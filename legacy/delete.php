<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';

require_login();

$user = current_user();
$id   = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    exit('参数错误');
}

$stmt = mysqli_prepare($connect,
    'DELETE FROM message WHERE id = ? AND user_id = ?');
mysqli_stmt_bind_param($stmt, 'ii', $id, $user['id']);
mysqli_stmt_execute($stmt);

header('Location: index.php');
exit;