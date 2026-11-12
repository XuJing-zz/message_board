<?php
require_once __DIR__ . '/../_common.php';

api_admin();
need_post();

$in = body();
$id = (int)($in['id'] ?? 0);
if ($id <= 0) fail('缺少 id');

$stmt = mysqli_prepare($connect, 'DELETE FROM article_comments WHERE id = ?');
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);

if (mysqli_stmt_affected_rows($stmt) === 0) fail('留言不存在', 404, 404);

ok(null, '删除成功');