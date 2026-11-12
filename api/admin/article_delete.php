<?php
require_once __DIR__ . '/../_common.php';

api_admin();
need_post();

$in = body();
$id = (int)($in['id'] ?? 0);
if ($id <= 0) fail('缺少 id');

// 先清关联数据（没有建外键，手动删）
$stmt = mysqli_prepare($connect, 'DELETE FROM article_likes    WHERE article_id = ?');
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);

$stmt = mysqli_prepare($connect, 'DELETE FROM article_comments WHERE article_id = ?');
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);

$stmt = mysqli_prepare($connect, 'DELETE FROM articles WHERE id = ?');
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);

if (mysqli_stmt_affected_rows($stmt) === 0) fail('文章不存在', 404, 404);

ok(null, '删除成功');