<?php
require_once __DIR__ . '/_common.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') fail('请用 POST 请求', 405);

$in       = body();
$username = trim($in['username'] ?? '');
$password = $in['password'] ?? '';

if ($username === '' || $password === '') fail('账号密码不能为空');

$stmt = mysqli_prepare($connect,
    'SELECT id, username, password FROM users WHERE username = ?');
if (!$stmt) fail('SQL 错误：' . mysqli_error($connect), 500, 500);

mysqli_stmt_bind_param($stmt, 's', $username);
mysqli_stmt_execute($stmt);
$user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$user || !password_verify($password, $user['password'])) {
    fail('用户名或密码错误', 1001);
}

$_SESSION['user'] = [
    'id'       => (int)$user['id'],
    'username' => $user['username'],
];

ok(['id' => (int)$user['id'], 'username' => $user['username']], '登录成功');