<?php
require_once __DIR__ . '/_common.php';

[$page, $size, $offset] = page_params();

$stmt = mysqli_prepare($connect,
    "SELECT COUNT(*) AS c FROM articles WHERE status = 'published'");
mysqli_stmt_execute($stmt);
$total = (int)mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['c'];

$sql = "SELECT a.id, a.title, a.cover, a.created_at,
               u.username AS author,
               (SELECT COUNT(*) FROM article_likes    l WHERE l.article_id = a.id) AS like_count,
               (SELECT COUNT(*) FROM article_comments c WHERE c.article_id = a.id) AS comment_count
        FROM articles a
        JOIN users u ON u.id = a.author_id
        WHERE a.status = 'published'
        ORDER BY a.id DESC
        LIMIT ? OFFSET ?";

$stmt = mysqli_prepare($connect, $sql);
mysqli_stmt_bind_param($stmt, 'ii', $size, $offset);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);

$list = [];
while ($row = mysqli_fetch_assoc($res)) {
    $row['id']            = (int)$row['id'];
    $row['like_count']    = (int)$row['like_count'];
    $row['comment_count'] = (int)$row['comment_count'];
    $list[] = $row;
}

ok(['list' => $list, 'total' => $total, 'page' => $page, 'size' => $size]);