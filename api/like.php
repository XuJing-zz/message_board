<?php
require_once __DIR__ . '/_common.php';

if (!current_user()) fail('请先登录', 401, 401);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') fail('请用 POST 请求', 405);

$in = body();
$id = (int)($in['id'] ?? 0);
if ($id <= 0) fail('参数错误');

$user = current_user();

$stmt = mysqli_prepare($connect,
    'SELECT id FROM likes WHERE message_id = ? AND user_id = ?');
if (!$stmt) fail('SQL 错误：' . mysqli_error($connect), 500, 500);
mysqli_stmt_bind_param($stmt, 'ii', $id, $user['id']);
mysqli_stmt_execute($stmt);
$exists = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if ($exists) {
    $stmt = mysqli_prepare($connect, 'DELETE FROM likes WHERE id = ?');
    mysqli_stmt_bind_param($stmt, 'i', $exists['id']);
    mysqli_stmt_execute($stmt);
    ok(['liked' => false], '已取消点赞');
}

$stmt = mysqli_prepare($connect,
    'INSERT INTO likes (user_id, message_id) VALUES (?, ?)');
mysqli_stmt_bind_param($stmt, 'ii', $user['id'], $id);
mysqli_stmt_execute($stmt);
ok(['liked' => true], '点赞成功');