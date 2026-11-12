<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';

require_login();

$user = current_user();
$id   = (int)($_POST['article_id'] ?? 0);
$back = $_POST['back'] ?? 'index.php';

if ($id > 0) {
    $stmt = mysqli_prepare($connect,
        'SELECT id FROM article_likes WHERE article_id = ? AND user_id = ?');
    mysqli_stmt_bind_param($stmt, 'ii', $id, $user['id']);
    mysqli_stmt_execute($stmt);
    $exists = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    if ($exists) {
        $stmt = mysqli_prepare($connect, 'DELETE FROM article_likes WHERE id = ?');
        mysqli_stmt_bind_param($stmt, 'i', $exists['id']);
        mysqli_stmt_execute($stmt);
    } else {
        $stmt = mysqli_prepare($connect,
            'INSERT INTO article_likes (article_id, user_id) VALUES (?, ?)');
        mysqli_stmt_bind_param($stmt, 'ii', $id, $user['id']);
        mysqli_stmt_execute($stmt);
    }
}

header('Location: ' . $back . '#like');
exit;