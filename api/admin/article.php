<?php
require_once __DIR__ . '/../_common.php';

api_admin();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) fail('缺少 id');

$sql = "SELECT a.*, u.username AS author,
               (SELECT COUNT(*) FROM article_likes    l WHERE l.article_id = a.id) AS like_count,
               (SELECT COUNT(*) FROM article_comments c WHERE c.article_id = a.id) AS comment_count
        FROM articles a
        JOIN users u ON u.id = a.author_id
        WHERE a.id = ?";

$stmt = mysqli_prepare($connect, $sql);
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$row) fail('文章不存在', 404, 404);

$row['id']            = (int)$row['id'];
$row['like_count']    = (int)$row['like_count'];
$row['comment_count'] = (int)$row['comment_count'];

ok($row);