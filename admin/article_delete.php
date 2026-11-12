<?php
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../db.php';

if (($_SESSION['user']['role'] ?? '') !== 'admin') {
    header('Location: login.php');
    exit;
}

$id = (int)($_GET['id'] ?? 0);
if ($id > 0) {
    foreach (['article_likes', 'article_comments'] as $t) {
        $stmt = mysqli_prepare($connect, "DELETE FROM $t WHERE article_id = ?");
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
    }
    $stmt = mysqli_prepare($connect, 'DELETE FROM articles WHERE id = ?');
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
}

header('Location: index.php');
exit;