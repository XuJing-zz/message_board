<?php
require_once __DIR__ . '/../_common.php';

need_post();

$in       = body();
$username = trim($in['username'] ?? '');
$password = $in['password'] ?? '';

if ($username === '' || $password === '') fail('账号密码不能为空');

$stmt = mysqli_prepare($connect,
    'SELECT id, username, password, role FROM users WHERE username = ?');
mysqli_stmt_bind_param($stmt, 's', $username);
mysqli_stmt_execute($stmt);
$user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$user || !password_verify($password, $user['password'])) {
    fail('用户名或密码错误', 1001);
}
if ($user['role'] !== 'admin') {
    fail('该账号不是管理员', 1003);
}

$_SESSION['user'] = [
    'id'       => (int)$user['id'],
    'username' => $user['username'],
    'role'     => $user['role'],
];

ok([
    'id'       => (int)$user['id'],
    'username' => $user['username'],
    'role'     => $user['role'],
], '登录成功');