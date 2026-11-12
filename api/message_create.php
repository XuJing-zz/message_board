<?php
require_once __DIR__ . '/_common.php';

if (!current_user()) fail('请先登录', 401, 401);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') fail('请用 POST 请求', 405);

$in      = body();
$content = trim($in['content'] ?? '');

if ($content === '')                     fail('留言不能为空');
if (mb_strlen($content, 'UTF-8') > 500)  fail('留言最多 500 字');

$user = current_user();

$stmt = mysqli_prepare($connect,
    'INSERT INTO message (user_id, content) VALUES (?, ?)');
if (!$stmt) fail('SQL 错误：' . mysqli_error($connect), 500, 500);

mysqli_stmt_bind_param($stmt, 'is', $user['id'], $content);
mysqli_stmt_execute($stmt);

ok(['id' => (int)mysqli_insert_id($connect)], '发布成功');