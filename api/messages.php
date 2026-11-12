<?php
require_once __DIR__ . '/_common.php';

$me   = current_user();
$myId = $me['id'] ?? 0;

$stmt = mysqli_prepare($connect, "
    SELECT m.id, m.user_id, m.content, m.created_at, u.username,
           (SELECT COUNT(*) FROM likes l  WHERE l.message_id = m.id) AS like_count,
           (SELECT COUNT(*) FROM likes l2 WHERE l2.message_id = m.id AND l2.user_id = ?) AS liked
    FROM message m
    JOIN users u ON u.id = m.user_id
    ORDER BY m.id DESC
");
if (!$stmt) fail('SQL 错误：' . mysqli_error($connect), 500, 500);

mysqli_stmt_bind_param($stmt, 'i', $myId);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);

$list = [];
while ($row = mysqli_fetch_assoc($res)) {
    $row['id']         = (int)$row['id'];
    $row['user_id']    = (int)$row['user_id'];
    $row['like_count'] = (int)$row['like_count'];
    $row['liked']      = (bool)$row['liked'];
    $list[] = $row;
}

ok(['list' => $list, 'total' => count($list), 'me' => $me]);