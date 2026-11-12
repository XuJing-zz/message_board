<?php
require_once __DIR__ . '/_common.php';

if (!current_user()) fail('请先登录', 401, 401);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') fail('请用 POST 请求', 405);

$in = body();
$id = (int)($in['id'] ?? 0);
if ($id <= 0) fail('参数错误');

$user = current_user();

$stmt = mysqli_prepare($connect,
    'DELETE FROM message WHERE id = ? AND user_id = ?');
mysqli_stmt_bind_param($stmt, 'ii', $id, $user['id']);
mysqli_stmt_execute($stmt);

if (mysqli_stmt_affected_rows($stmt) === 0) {
    fail('留言不存在或不属于你', 404, 404);
}

ok(null, '删除成功');