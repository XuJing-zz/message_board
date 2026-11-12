<?php
require_once __DIR__ . '/_common.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) fail('缺少 id');

$me   = current_user();
$myId = $me['id'] ?? 0;

$sql = "SELECT a.id, a.title, a.cover, a.content, a.created_at, a.updated_at,
               u.username AS author,
               (SELECT COUNT(*) FROM article_likes    l WHERE l.article_id = a.id) AS like_count,
               (SELECT COUNT(*) FROM article_comments c WHERE c.article_id = a.id) AS comment_count,
               (SELECT COUNT(*) FROM article_likes   l2 WHERE l2.article_id = a.id AND l2.user_id = ?) AS liked
        FROM articles a
        JOIN users u ON u.id = a.author_id
        WHERE a.id = ? AND a.status = 'published'";

$stmt = mysqli_prepare($connect, $sql);
mysqli_stmt_bind_param($stmt, 'ii', $myId, $id);
mysqli_stmt_execute($stmt);
$row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$row) fail('文章不存在或未发布', 404, 404);

$row['id']            = (int)$row['id'];
$row['like_count']    = (int)$row['like_count'];
$row['comment_count'] = (int)$row['comment_count'];
$row['liked']         = (bool)$row['liked'];

ok($row);