<?php
require_once __DIR__ . '/_common.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') fail('请用 POST 请求', 405);

$in       = body();
$username = trim($in['username'] ?? '');
$password = $in['password'] ?? '';

if ($username === '')                    fail('用户名不能为空');
if (mb_strlen($username, 'UTF-8') > 20)  fail('用户名不能超过 20 个字符');
if (mb_strlen($password, 'UTF-8') < 6)   fail('密码至少 6 位');

$stmt = mysqli_prepare($connect, 'SELECT id FROM users WHERE username = ?');
mysqli_stmt_bind_param($stmt, 's', $username);
mysqli_stmt_execute($stmt);
if (mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))) {
    fail('用户名已存在', 1002);
}

$hash = password_hash($password, PASSWORD_DEFAULT);
$stmt = mysqli_prepare($connect,
    'INSERT INTO users (username, password) VALUES (?, ?)');
mysqli_stmt_bind_param($stmt, 'ss', $username, $hash);
mysqli_stmt_execute($stmt);

$id = (int)mysqli_insert_id($connect);
$_SESSION['user'] = ['id' => $id, 'username' => $username];

ok(['id' => $id, 'username' => $username], '注册成功');