<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';

require_login();

$user = current_user();
$id   = (int)($_POST['id'] ?? 0);

if ($id > 0) {
    $stmt = mysqli_prepare($connect,
        'SELECT id FROM likes WHERE message_id = ? AND user_id = ?');
    if (!$stmt) {
        die('SQL 错误：' . mysqli_error($connect));
    }
    mysqli_stmt_bind_param($stmt, 'ii', $id, $user['id']);
    mysqli_stmt_execute($stmt);
    $exists = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    if ($exists) {
        $stmt = mysqli_prepare($connect, 'DELETE FROM likes WHERE id = ?');
        mysqli_stmt_bind_param($stmt, 'i', $exists['id']);
        mysqli_stmt_execute($stmt);
    } else {
        $stmt = mysqli_prepare($connect,
            'INSERT INTO likes (user_id, message_id) VALUES (?, ?)');
        mysqli_stmt_bind_param($stmt, 'ii', $user['id'], $id);
        mysqli_stmt_execute($stmt);
    }
}

header('Location: index.php#msg' . $id);
exit;